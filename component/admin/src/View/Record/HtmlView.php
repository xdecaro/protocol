<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Record;

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
        $model = $this->getModel();
        $this->form = $model->getForm();
        $this->item = $model->getItem();

        if (!$this->form) {
            throw new \RuntimeException($model->getError());
        }

        $isDraft = empty($this->item->id) || (string) $this->item->status === 'draft';
        if (!$isDraft) {
            foreach (array('register_id', 'direction', 'subject', 'document_date', 'external_reference', 'sender', 'recipients', 'notes') as $field) {
                $this->form->setFieldAttribute($field, 'disabled', 'true');
            }
        }

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        try {
            Factory::getContainer()->get(CoreIntegrationService::class)->enableUi($wa);
        } catch (\Throwable) {
            // Core is optional; local Protocol styling remains available.
        }
        $wa->useStyle('com_decaroprotocol.admin');

        $this->addToolbar($isDraft);
        parent::display($tpl);
    }

    protected function addToolbar($isDraft)
    {
        $user = Factory::getApplication()->getIdentity();
        ToolbarHelper::title(empty($this->item->id) ? Text::_('COM_DECAROPROTOCOL_RECORD_NEW') : Text::_('COM_DECAROPROTOCOL_RECORD_EDIT'), 'file-2');

        if ($isDraft) {
            $action = empty($this->item->id) ? 'core.create' : 'core.edit';
            if ($user->authorise($action, 'com_decaroprotocol')) {
                ToolbarHelper::apply('record.apply');
                ToolbarHelper::save('record.save');
            }
            if ($user->authorise('protocol.protocol', 'com_decaroprotocol')) {
                ToolbarHelper::custom('record.protocol', 'checkin', 'checkin', 'COM_DECAROPROTOCOL_PROTOCOL', false);
            }
        }

        ToolbarHelper::cancel('record.cancel', 'JTOOLBAR_CLOSE');
    }
}
