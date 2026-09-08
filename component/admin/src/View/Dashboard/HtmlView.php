<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $stats;

    public function display($tpl = null)
    {
        $this->stats = $this->getModel()->getStats();
        Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('com_decaroprotocol.admin');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_DECAROPROTOCOL_DASHBOARD'), 'file-2');
        $user = Factory::getApplication()->getIdentity();
        if ($user->authorise('core.create', 'com_decaroprotocol')) {
            ToolbarHelper::addNew('record.add', 'COM_DECAROPROTOCOL_NEW_RECORD');
        }
        if ($user->authorise('core.admin', 'com_decaroprotocol')) {
            ToolbarHelper::preferences('com_decaroprotocol');
        }
    }
}
