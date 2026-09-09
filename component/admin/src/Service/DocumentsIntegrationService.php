<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;
use Throwable;
use xdecaro\Core\Integration\EntityReference;
use xdecaro\Core\Integration\RelationReference;

/**
 * Optional Protocol -> Documents adapter.
 *
 * Protocol owns validation and authorization of its record references.
 * Documents owns document persistence, ACL and relation persistence.
 * No Documents table is read here.
 */
final class DocumentsIntegrationService
{
    public const DOCUMENTS_COMPONENT = 'com_decarodocuments';
    public const MINIMUM_DOCUMENTS_VERSION = '1.2.1';
    public const PROTOCOL_COMPONENT = 'com_decaroprotocol';
    public const PROTOCOL_ENTITY = 'record';
    public const DEFAULT_RELATION_TYPE = 'attachment';

    public function __construct(
        private CoreIntegrationService $core,
        private DatabaseInterface $db
    ) {
    }

    public function isAvailable(): bool
    {
        if (!$this->core->isAvailable()) {
            return false;
        }

        try {
            $service = $this->getDocumentsRelationService();

            return method_exists($service, 'attach')
                && method_exists($service, 'detach')
                && method_exists($service, 'findDocuments');
        } catch (Throwable) {
            return false;
        }
    }

    public function attachDocumentToRecord(
        int $documentId,
        int $recordId,
        string $relationType = self::DEFAULT_RELATION_TYPE
    ): void {
        $this->assertProtocolPermission('core.edit');
        $this->assertPositiveId($documentId, 'document');
        $this->assertPositiveId($recordId, 'record');
        $this->assertRecordRelationsMutable($recordId);

        $this->getDocumentsRelationService()->attach(
            $this->createDocumentRelation($documentId, $recordId, $relationType)
        );
    }

    public function detachDocumentFromRecord(
        int $documentId,
        int $recordId,
        string $relationType = self::DEFAULT_RELATION_TYPE
    ): void {
        $this->assertProtocolPermission('core.edit');
        $this->assertPositiveId($documentId, 'document');
        $this->assertPositiveId($recordId, 'record');
        $this->assertRecordRelationsMutable($recordId);

        $this->getDocumentsRelationService()->detach(
            $this->createDocumentRelation($documentId, $recordId, $relationType)
        );
    }

    /** @return array<int,array<string,mixed>> */
    public function findDocumentsForRecord(
        int $recordId,
        ?string $relationType = self::DEFAULT_RELATION_TYPE
    ): array {
        $this->assertProtocolPermission('core.manage');
        $this->assertPositiveId($recordId, 'record');
        $this->assertRecordExists($recordId);

        $target = $this->core->createEntityReference(self::PROTOCOL_ENTITY, $recordId);
        $relationType = $relationType === null ? null : $this->normaliseRelationType($relationType);

        return $this->getDocumentsRelationService()->findDocuments($target, $relationType);
    }

    private function createDocumentRelation(int $documentId, int $recordId, string $relationType): RelationReference
    {
        if (!$this->core->isAvailable()) {
            throw new RuntimeException(
                'Protocol document integration requires a compatible Core by xdecaro 1.3.0+ installation.'
            );
        }

        $document = new EntityReference(self::DOCUMENTS_COMPONENT, 'document', $documentId);
        $record = $this->core->createEntityReference(self::PROTOCOL_ENTITY, $recordId);

        return new RelationReference($document, $record, $this->normaliseRelationType($relationType));
    }

    private function getDocumentsRelationService(): object
    {
        if (!$this->core->isAvailable()) {
            throw new RuntimeException(
                'Protocol document integration requires a compatible Core by xdecaro 1.3.0+ installation.'
            );
        }

        $this->assertDocumentsVersionCompatible();

        try {
            $application = Factory::getApplication();

            if (!method_exists($application, 'bootComponent')) {
                throw new RuntimeException('The current Joomla application cannot boot Components.');
            }

            $component = $application->bootComponent(self::DOCUMENTS_COMPONENT);

            if (!is_object($component) || !method_exists($component, 'getRelationService')) {
                throw new RuntimeException('Documents 1.2.1+ public relation API is not available.');
            }

            $service = $component->getRelationService();

            if (!is_object($service)
                || !method_exists($service, 'attach')
                || !method_exists($service, 'detach')
                || !method_exists($service, 'findDocuments')
            ) {
                throw new RuntimeException('Documents 1.2.1+ public relation API is incomplete.');
            }

            return $service;
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Documents 1.2.1+ is unavailable or could not be booted.',
                0,
                $exception
            );
        }
    }

    private function assertDocumentsVersionCompatible(): void
    {
        $type = 'component';
        $element = self::DOCUMENTS_COMPONENT;
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('manifest_cache'),
                $this->db->quoteName('enabled'),
            ])
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('type') . ' = :type')
            ->where($this->db->quoteName('element') . ' = :element')
            ->bind(':type', $type)
            ->bind(':element', $element);

        $extension = $this->db->setQuery($query, 0, 1)->loadObject();
        if (!$extension || (int) ($extension->enabled ?? 0) !== 1) {
            throw new RuntimeException('Documents 1.2.1+ is not installed and enabled.');
        }

        $manifest = json_decode((string) ($extension->manifest_cache ?? ''), true);
        $version = is_array($manifest) ? trim((string) ($manifest['version'] ?? '')) : '';

        if ($version === '' || version_compare($version, self::MINIMUM_DOCUMENTS_VERSION, '<')) {
            throw new RuntimeException('Documents 1.2.1+ is required for Protocol document integration.');
        }
    }

    private function assertProtocolPermission(string $action): void
    {
        $identity = Factory::getApplication()->getIdentity();

        if (!$identity || !$identity->authorise($action, self::PROTOCOL_COMPONENT)) {
            throw new RuntimeException('Not authorised to manage Protocol document relations.', 403);
        }
    }

    private function assertRecordExists(int $recordId): void
    {
        if ($this->getRecordStatus($recordId) === null) {
            throw new RuntimeException('The referenced Protocol record does not exist.');
        }
    }

    private function assertRecordRelationsMutable(int $recordId): void
    {
        $status = $this->getRecordStatus($recordId);

        if ($status === null) {
            throw new RuntimeException('The referenced Protocol record does not exist.');
        }

        if ($status !== 'draft') {
            throw new RuntimeException(
                'Documents attached to a protocolled record are immutable; use a tracked rectification workflow.'
            );
        }
    }

    private function getRecordStatus(int $recordId): ?string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('status'))
            ->from($this->db->quoteName('#__decaroprotocol_records'))
            ->where($this->db->quoteName('id') . ' = :recordId')
            ->bind(':recordId', $recordId, ParameterType::INTEGER);

        $status = $this->db->setQuery($query, 0, 1)->loadResult();

        return $status === null ? null : (string) $status;
    }

    private function assertPositiveId(int $id, string $label): void
    {
        if ($id < 1) {
            throw new RuntimeException('Invalid ' . $label . ' ID.');
        }
    }

    private function normaliseRelationType(string $relationType): string
    {
        $relationType = trim($relationType);

        if (!preg_match('/^[a-z][a-z0-9_]{0,63}$/', $relationType)) {
            throw new RuntimeException('Invalid document relation type.');
        }

        return $relationType;
    }
}
