<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class RecordsModel extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array('id', 'r.id', 'protocol_number', 'r.protocol_number', 'protocol_year', 'r.protocol_year', 'subject', 'r.subject', 'status', 'r.status', 'direction', 'r.direction', 'created', 'r.created');
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'r.created', $direction = 'DESC')
    {
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.status', $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status'));
        $this->setState('filter.direction', $this->getUserStateFromRequest($this->context . '.filter.direction', 'filter_direction'));
        $this->setState('filter.register_id', (int) $this->getUserStateFromRequest($this->context . '.filter.register_id', 'filter_register_id'));
        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('r.*')
            ->select($db->quoteName('reg.title', 'register_title'))
            ->from($db->quoteName('#__decaroprotocol_records', 'r'))
            ->leftJoin($db->quoteName('#__decaroprotocol_registers', 'reg') . ' ON ' . $db->quoteName('reg.id') . ' = ' . $db->quoteName('r.register_id'));

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $like = '%' . $db->escape($search, true) . '%';
            $conditions = array(
                $db->quoteName('r.subject') . ' LIKE :search_subject',
                $db->quoteName('r.sender') . ' LIKE :search_sender',
                $db->quoteName('r.recipients') . ' LIKE :search_recipients',
                $db->quoteName('r.external_reference') . ' LIKE :search_reference',
            );
            if (ctype_digit($search)) {
                $conditions[] = $db->quoteName('r.protocol_number') . ' = ' . (int) $search;
            }
            $query->where('(' . implode(' OR ', $conditions) . ')');
            $query->bind(':search_subject', $like)
                ->bind(':search_sender', $like)
                ->bind(':search_recipients', $like)
                ->bind(':search_reference', $like);
        }

        $status = (string) $this->getState('filter.status');
        if ($status !== '') {
            $query->where($db->quoteName('r.status') . ' = :status')->bind(':status', $status);
        }

        $direction = (string) $this->getState('filter.direction');
        if ($direction !== '') {
            $query->where($db->quoteName('r.direction') . ' = :direction')->bind(':direction', $direction);
        }

        $registerId = (int) $this->getState('filter.register_id');
        if ($registerId > 0) {
            $query->where($db->quoteName('r.register_id') . ' = ' . $registerId);
        }

        $orderCol = $this->state->get('list.ordering', 'r.created');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
