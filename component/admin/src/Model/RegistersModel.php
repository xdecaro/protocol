<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class RegistersModel extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array('id', 'title', 'code', 'active', 'ordering');
        }
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('reg.*')
            ->from($db->quoteName('#__decaroprotocol_registers', 'reg'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $like = '%' . $db->escape($search, true) . '%';
            $query->where('(' . $db->quoteName('reg.title') . ' LIKE :search_title OR ' . $db->quoteName('reg.code') . ' LIKE :search_code)')
                ->bind(':search_title', $like)
                ->bind(':search_code', $like);
        }

        $query->order($db->quoteName('reg.ordering') . ' ASC, ' . $db->quoteName('reg.title') . ' ASC');
        return $query;
    }

    protected function populateState($ordering = 'ordering', $direction = 'ASC')
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        parent::populateState($ordering, $direction);
    }
}
