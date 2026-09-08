<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class DashboardModel extends BaseDatabaseModel
{
    public function getStats()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select(array(
                'COUNT(*) AS total',
                "SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) AS drafts",
                "SUM(CASE WHEN status='protocolled' THEN 1 ELSE 0 END) AS protocolled",
                "SUM(CASE WHEN direction='incoming' THEN 1 ELSE 0 END) AS incoming",
                "SUM(CASE WHEN direction='outgoing' THEN 1 ELSE 0 END) AS outgoing",
                "SUM(CASE WHEN direction='internal' THEN 1 ELSE 0 END) AS internal",
            ))
            ->from($db->quoteName('#__decaroprotocol_records'));

        $row = $db->setQuery($query)->loadAssoc();
        $defaults = array('total' => 0, 'drafts' => 0, 'protocolled' => 0, 'incoming' => 0, 'outgoing' => 0, 'internal' => 0);
        return array_map('intval', array_merge($defaults, (array) $row));
    }
}
