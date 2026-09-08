<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Throwable;

final class InformationHelper
{
    public const VERSION = '1.2.0';
    public const MINIMUM_JOOMLA = '4.4.0';
    public const MINIMUM_PHP = '8.1.0';
    public const MINIMUM_CORE = '1.3.0';

    private const EXPECTED_TABLES = [
        '#__decaroprotocol_registers',
        '#__decaroprotocol_register_counters',
        '#__decaroprotocol_records',
        '#__decaroprotocol_audit',
    ];

    private const CONNECTED_COMPONENTS = [
        'Forms' => 'com_decaroforms',
        'Documents' => 'com_decarodocuments',
        'Courses' => 'com_decarocourses',
        'Competitions' => 'com_decarodcl',
        'Membership' => 'com_decaromembership',
    ];

    public static function getData(): array
    {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();

        $component = self::getExtension($db, 'component', 'com_decaroprotocol');
        $package = self::getExtension($db, 'package', 'pkg_decaroprotocol')
            ?? self::getExtension($db, 'package', 'decaroprotocol');

        $componentVersion = self::manifestVersion($component) ?: self::VERSION;
        $packageVersion = self::manifestVersion($package);
        $schemaVersion = self::getSchemaVersion($db, (int) ($component->extension_id ?? 0));

        $presentTables = 0;
        foreach (self::EXPECTED_TABLES as $table) {
            if (in_array(str_replace('#__', $prefix, $table), $tables, true)) {
                ++$presentTables;
            }
        }

        $schemaAligned = $schemaVersion !== '' && version_compare($schemaVersion, $componentVersion, '>=');
        $packageDetected = $package !== null && $packageVersion !== '';
        $installationConsistent = $packageDetected
            && version_compare($componentVersion, $packageVersion, '==')
            && $schemaAligned
            && $presentTables === count(self::EXPECTED_TABLES);

        $joomlaVersion = defined('JVERSION') ? (string) JVERSION : '';
        $phpVersion = PHP_VERSION;
        $environmentCompatible = version_compare($joomlaVersion, self::MINIMUM_JOOMLA, '>=')
            && version_compare($phpVersion, self::MINIMUM_PHP, '>=');

        $databaseType = '';
        $databaseVersion = '';
        try {
            $databaseType = method_exists($db, 'getServerType') ? (string) $db->getServerType() : '';
            $databaseVersion = method_exists($db, 'getVersion') ? (string) $db->getVersion() : '';
        } catch (Throwable) {
            // Diagnostic only: do not block the component.
        }

        $coreVersion = class_exists(\xdecaro\Core\Version::class)
            ? trim((string) \xdecaro\Core\Version::VERSION)
            : '';
        $coreContracts = class_exists(\xdecaro\Core\Integration\EntityReference::class)
            && class_exists(\xdecaro\Core\Integration\RelationReference::class);
        $coreAssets = class_exists(\xdecaro\Core\Asset\AssetService::class);

        $connected = [];
        foreach (self::CONNECTED_COMPONENTS as $label => $element) {
            $extension = self::getExtension($db, 'component', $element);
            $connected[] = [
                'label' => $label,
                'element' => $element,
                'installed' => $extension !== null,
                'enabled' => (int) ($extension->enabled ?? 0) === 1,
                'version' => self::manifestVersion($extension),
            ];
        }

        $criticalChecks = [
            'tablesPresent' => $presentTables === count(self::EXPECTED_TABLES),
            'schemaAligned' => $schemaAligned,
            'packageDetected' => $packageDetected,
            'installationConsistent' => $installationConsistent,
            'environmentCompatible' => $environmentCompatible,
        ];
        $criticalCount = count(array_filter($criticalChecks, static fn (bool $ok): bool => !$ok));

        return [
            'componentVersion' => $componentVersion,
            'packageVersion' => $packageVersion,
            'schemaVersion' => $schemaVersion,
            'minimumJoomla' => self::MINIMUM_JOOMLA,
            'minimumPhp' => self::MINIMUM_PHP,
            'joomlaVersion' => $joomlaVersion,
            'phpVersion' => $phpVersion,
            'databaseType' => $databaseType,
            'databaseVersion' => $databaseVersion,
            'tablePresentCount' => $presentTables,
            'tableExpectedCount' => count(self::EXPECTED_TABLES),
            'coreVersion' => $coreVersion,
            'coreContracts' => $coreContracts,
            'coreAssets' => $coreAssets,
            'coreCompatible' => $coreVersion !== '' && version_compare($coreVersion, self::MINIMUM_CORE, '>='),
            'connectedComponents' => $connected,
            'diagnostics' => $criticalChecks,
            'criticalCount' => $criticalCount,
            'systemOk' => $criticalCount === 0,
        ];
    }

    private static function getExtension(DatabaseInterface $db, string $type, string $element): ?object
    {
        try {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('extension_id'),
                    $db->quoteName('manifest_cache'),
                    $db->quoteName('enabled'),
                ])
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = :type')
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':type', $type)
                ->bind(':element', $element);

            $record = $db->setQuery($query, 0, 1)->loadObject();

            return $record ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function manifestVersion(?object $extension): string
    {
        if (!$extension || empty($extension->manifest_cache)) {
            return '';
        }

        $manifest = json_decode((string) $extension->manifest_cache, true);

        return is_array($manifest) ? trim((string) ($manifest['version'] ?? '')) : '';
    }

    private static function getSchemaVersion(DatabaseInterface $db, int $extensionId): string
    {
        if ($extensionId <= 0) {
            return '';
        }

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('version_id'))
                ->from($db->quoteName('#__schemas'))
                ->where($db->quoteName('extension_id') . ' = :extensionId')
                ->bind(':extensionId', $extensionId, ParameterType::INTEGER);

            return trim((string) ($db->setQuery($query, 0, 1)->loadResult() ?? ''));
        } catch (Throwable) {
            return '';
        }
    }
}
