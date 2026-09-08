<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Registers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    public $items;
    public $pagination;
    public $state;
    public $filterForm;
    public $activeFilters;

    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('protocol.manage_registers', 'com_decaroprotocol') && !$user->authorise('core.admin', 'com_decaroprotocol')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('com_decaroprotocol.admin');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_DECAROPROTOCOL_REGISTERS'), 'folder');
        ToolbarHelper::addNew('register.add');
        ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'registers.delete');
    }
}
