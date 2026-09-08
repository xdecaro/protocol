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
    private const MINIMUM_CORE_VERSION = '1.1.0';

    public function isAvailable(): bool
    {
        return class_exists(\Xdecaro\Core\Integration\EntityReference::class)
            && class_exists(\Xdecaro\Core\Integration\RelationReference::class);
    }

    public function getVersion(): string
    {
        return class_exists(\Xdecaro\Core\Version::class)
            ? (string) \Xdecaro\Core\Version::VERSION
            : '';
    }

    public function isUiCompatible(): bool
    {
        $version = $this->getVersion();

        return $version !== ''
            && version_compare($version, self::MINIMUM_CORE_VERSION, '>=')
            && class_exists(\Xdecaro\Core\Asset\AssetService::class);
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
            $service = new \Xdecaro\Core\Asset\AssetService();

            return $service->useComponents($webAssets);
        } catch (\Throwable) {
            return false;
        }
    }

    public function createEntityReference(string $entity, int|string $id): object
    {
        $this->assertAvailable();

        return new \Xdecaro\Core\Integration\EntityReference(
            self::COMPONENT,
            $entity,
            $id
        );
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

        $source = new \Xdecaro\Core\Integration\EntityReference(
            self::COMPONENT,
            $sourceEntity,
            $sourceId
        );

        $target = new \Xdecaro\Core\Integration\EntityReference(
            $targetComponent,
            $targetEntity,
            $targetId
        );

        return new \Xdecaro\Core\Integration\RelationReference(
            $source,
            $target,
            $relationType
        );
    }

    private function assertAvailable(): void
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException(
                'Core by xdecaro integration is unavailable. Install a compatible Core before using cross-product references.'
            );
        }
    }
}
