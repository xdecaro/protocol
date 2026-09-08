<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

class RegisterModel extends AdminModel
{
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_decaroprotocol.register', 'register', array('control' => 'jform', 'load_data' => $loadData));
        return $form ?: false;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_decaroprotocol.edit.register.data', array());
        return empty($data) ? $this->getItem() : $data;
    }

    public function delete(&$pks)
    {
        $db = $this->getDatabase();
        foreach ((array) $pks as $pk) {
            $pk = (int) $pk;
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__decaroprotocol_records'))
                ->where($db->quoteName('register_id') . ' = ' . $pk);
            if ((int) $db->setQuery($query)->loadResult() > 0) {
                $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_REGISTER_IN_USE'));
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
