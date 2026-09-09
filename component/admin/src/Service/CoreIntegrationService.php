<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\WebAsset\WebAssetManager;

/**
 * Optional adapter between Protocol and public Core by xdecaro contracts.
 * Protocol domain logic must never depend on Core internals.
 */
final class CoreIntegrationService
{
    private const COMPONENT = 'com_decaroprotocol';
    private const MINIMUM_CORE_VERSION = '1.4.0';

    public function isAvailable(): bool
    {
        $version = $this->getVersion();

        return $version !== ''
            && version_compare($version, self::MINIMUM_CORE_VERSION, '>=')
            && class_exists(\xdecaro\Core\Integration\EntityReference::class)
            && class_exists(\xdecaro\Core\Integration\RelationReference::class);
    }

    public function getVersion(): string
    {
        return class_exists(\xdecaro\Core\Version::class)
            ? trim((string) \xdecaro\Core\Version::VERSION)
            : '';
    }

    public function isUiCompatible(): bool
    {
        $version = $this->getVersion();

        return $version !== ''
            && version_compare($version, self::MINIMUM_CORE_VERSION, '>=')
            && class_exists(\xdecaro\Core\Asset\AssetService::class);
    }

    public function hasCapabilityRegistry(): bool
    {
        return class_exists(\xdecaro\Core\Integration\Capability::class)
            && class_exists(\xdecaro\Core\Integration\CapabilityRegistry::class);
    }

    /** @return array<int,object> */
    public function getCapabilities(): array
    {
        if (!$this->hasCapabilityRegistry()) {
            return [];
        }

        return [
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.records', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.protocolize', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.query', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.documents', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.analytics.provider', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.notifications.bridge', '1'),
            new \xdecaro\Core\Integration\Capability(self::COMPONENT, 'protocol.tasks.bridge', '1'),
        ];
    }

    public function registerCapabilities(object $registry): void
    {
        if (!$this->hasCapabilityRegistry() || !$registry instanceof \xdecaro\Core\Integration\CapabilityRegistry) {
            throw new \InvalidArgumentException('A Core 1.4 CapabilityRegistry is required.');
        }

        $registry->registerMany($this->getCapabilities());
    }

    /**
     * Enables shared Core UI primitives when a compatible Core is installed.
     * Returns false instead of failing because Core is an optional dependency.
     */
    public function enableUi(WebAssetManager $webAssets): bool
    {
        if (!$this->isUiCompatible()) {
            return false;
        }

        try {
            $service = new \xdecaro\Core\Asset\AssetService();

            return $service->useComponents($webAssets);
        } catch (\Throwable) {
            return false;
        }
    }

    public function createEntityReference(string $entity, int|string $id): object
    {
        $this->assertAvailable();

        return new \xdecaro\Core\Integration\EntityReference(self::COMPONENT, $entity, $id);
    }

    public function createRelationReference(
        string $sourceEntity,
        int|string $sourceId,
        string $targetComponent,
        string $targetEntity,
        int|string $targetId,
        string $relationType
    ): object {
        $this->assertAvailable();

        $source = new \xdecaro\Core\Integration\EntityReference(self::COMPONENT, $sourceEntity, $sourceId);
        $target = new \xdecaro\Core\Integration\EntityReference(
            $targetComponent,
            $targetEntity,
            $targetId
        );

        return new \xdecaro\Core\Integration\RelationReference($source, $target, $relationType);
    }

    private function assertAvailable(): void
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException(
                'Core by xdecaro 1.4.0+ integration is unavailable. Install a compatible Core before using cross-product references.'
            );
        }
    }
}
