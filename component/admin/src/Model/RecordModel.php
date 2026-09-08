<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

class RecordModel extends AdminModel
{
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_decaroprotocol.record', 'record', array('control' => 'jform', 'load_data' => $loadData));
        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_decaroprotocol.edit.record.data', array());
        return empty($data) ? $this->getItem() : $data;
    }

    public function save($data)
    {
        $id = (int) ($data['id'] ?? 0);
        $allowedDirections = array('incoming', 'outgoing', 'internal');
        $direction = isset($data['direction']) ? (string) $data['direction'] : 'incoming';

        if (!in_array($direction, $allowedDirections, true)) {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_DIRECTION'));
            return false;
        }

        if ($id > 0) {
            $table = $this->getTable();
            if (!$table->load($id)) {
                $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_RECORD_NOT_FOUND'));
                return false;
            }

            if ((string) $table->status !== 'draft') {
                $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_IMMUTABLE'));
                return false;
            }
        }

        unset($data['protocol_number'], $data['protocol_year'], $data['protocolled_at'], $data['protocolled_by']);
        $data['status'] = 'draft';
        $data['direction'] = $direction;

        return parent::save($data);
    }

    public function delete(&$pks)
    {
        foreach ((array) $pks as $pk) {
            $table = $this->getTable();
            if ($table->load((int) $pk) && (string) $table->status !== 'draft') {
                $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_DELETE_PROTOCOLLED'));
                return false;
            }
        }

        return parent::delete($pks);
    }

    protected function prepareTable($table)
    {
        $now = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        if (empty($table->id)) {
            $table->created = $now;
            $table->created_by = $userId;
        }

        $table->modified = $now;
        $table->modified_by = $userId;
    }
}
