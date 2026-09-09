<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class pkg_decaroprotocolInstallerScript
{
    public function postflight($type, $parent): void
    {
        // Preserve the administrator's enabled/disabled choice during upgrades.
        if (!in_array((string) $type, ['install', 'discover_install'], true)) {
            return;
        }

        try {
            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $enabled = 1;
            $pluginType = 'plugin';
            $folder = 'xdecaroanalytics';
            $element = 'decaroprotocol';
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = :enabled')
                ->where($db->quoteName('type') . ' = :type')
                ->where($db->quoteName('folder') . ' = :folder')
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':enabled', $enabled, ParameterType::INTEGER)
                ->bind(':type', $pluginType)
                ->bind(':folder', $folder)
                ->bind(':element', $element);
            $db->setQuery($query)->execute();
        } catch (\Throwable $exception) {
            Log::add(
                'Protocol optional Analytics plugin could not be enabled automatically: ' . $exception->getMessage(),
                Log::WARNING,
                'com_decaroprotocol.integration'
            );
        }
    }
}
