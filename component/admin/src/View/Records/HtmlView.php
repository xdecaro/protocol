<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Records;

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
        $model = $this->getModel();
        $this->items = $model->getItems();
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();
        $this->filterForm = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (count($errors = $model->getErrors())) {
            throw new \RuntimeException(implode("\n", $errors));
        }

        Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('com_decaroprotocol.admin');
        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_DECAROPROTOCOL_RECORDS'), 'file-2');
        $user = Factory::getApplication()->getIdentity();
        if ($user->authorise('core.create', 'com_decaroprotocol')) {
            ToolbarHelper::addNew('record.add');
        }
        if ($user->authorise('core.delete', 'com_decaroprotocol')) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'records.delete');
        }
    }
}
