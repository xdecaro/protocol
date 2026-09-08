<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class RecordTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__decaroprotocol_records', 'id', $db);
    }

    public function check()
    {
        $this->subject = trim((string) $this->subject);
        $this->direction = trim((string) $this->direction);
        $this->document_date = trim((string) $this->document_date) === '' ? null : $this->document_date;
        $allowed = array('incoming', 'outgoing', 'internal');

        if ((int) $this->register_id < 1) {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_REGISTER_REQUIRED'));
            return false;
        }

        if ($this->subject === '') {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_SUBJECT_REQUIRED'));
            return false;
        }

        if (!in_array($this->direction, $allowed, true)) {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_DIRECTION'));
            return false;
        }

        return parent::check();
    }
}
