<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class RegisterTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__decaroprotocol_registers', 'id', $db);
    }

    public function check()
    {
        $this->title = trim((string) $this->title);
        $this->code = strtolower(trim((string) $this->code));

        if ($this->title === '' || $this->code === '') {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_REGISTER_FIELDS'));
            return false;
        }

        if (!preg_match('/^[a-z0-9_-]+$/', $this->code)) {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_REGISTER_CODE'));
            return false;
        }

        if ($this->numbering_mode !== 'yearly') {
            $this->setError(Text::_('COM_DECAROPROTOCOL_ERROR_NUMBERING_MODE'));
            return false;
        }

        return parent::check();
    }
}
