<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Register;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CoreIntegrationService;

class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;

    public function display($tpl = null)
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('protocol.manage_registers', 'com_decaroprotocol') && !$user->authorise('core.admin', 'com_decaroprotocol')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();
        $this->form = $model->getForm();
        $this->item = $model->getItem();

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        try {
            Factory::getContainer()->get(CoreIntegrationService::class)->enableUi($wa);
        } catch (\Throwable) {
            // Core is optional; local Protocol styling remains available.
        }
        $wa->useStyle('com_decaroprotocol.admin');

        ToolbarHelper::title(empty($this->item->id) ? Text::_('COM_DECAROPROTOCOL_REGISTER_NEW') : Text::_('COM_DECAROPROTOCOL_REGISTER_EDIT'), 'folder');
        ToolbarHelper::apply('register.apply');
        ToolbarHelper::save('register.save');
        ToolbarHelper::cancel('register.cancel', 'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }
}
