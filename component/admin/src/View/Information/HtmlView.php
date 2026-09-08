<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\View\Information;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Xdecaro\Component\Decaroprotocol\Administrator\Helper\InformationHelper;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CoreIntegrationService;

class HtmlView extends BaseHtmlView
{
    public array $info = [];
    public bool $coreUiActive = false;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();

        if (!$user->authorise('core.manage', 'com_decaroprotocol')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        ToolbarHelper::title(Text::_('COM_DECAROPROTOCOL_INFORMATION'), 'info-circle');

        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_decaroprotocol');

        try {
            $this->coreUiActive = Factory::getContainer()
                ->get(CoreIntegrationService::class)
                ->enableUi($wa);
        } catch (\Throwable) {
            $this->coreUiActive = false;
        }

        $wa->useStyle('com_decaroprotocol.admin');
        $wa->useStyle('com_decaroprotocol.information');

        $this->info = InformationHelper::getData();

        parent::display($tpl);
    }
}
