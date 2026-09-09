<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\MVCComponent;
use LogicException;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\AnalyticsSourceService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CoreIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CrossProductIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\DocumentsIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\ProtocolService;

final class ProtocolComponent extends MVCComponent
{
    private ?ProtocolService $protocolService = null;
    private ?CoreIntegrationService $coreIntegrationService = null;
    private ?DocumentsIntegrationService $documentsIntegrationService = null;
    private ?AnalyticsSourceService $analyticsSourceService = null;
    private ?CrossProductIntegrationService $crossProductIntegrationService = null;

    public function setProtocolService(ProtocolService $service): void { $this->protocolService = $service; }
    public function setCoreIntegrationService(CoreIntegrationService $service): void { $this->coreIntegrationService = $service; }
    public function setDocumentsIntegrationService(DocumentsIntegrationService $service): void { $this->documentsIntegrationService = $service; }
    public function setAnalyticsSourceService(AnalyticsSourceService $service): void { $this->analyticsSourceService = $service; }
    public function setCrossProductIntegrationService(CrossProductIntegrationService $service): void { $this->crossProductIntegrationService = $service; }

    public function getProtocolService(): ProtocolService
    {
        return $this->protocolService ?? throw new LogicException('Protocol service has not been initialized.');
    }

    public function getCoreIntegrationService(): CoreIntegrationService
    {
        return $this->coreIntegrationService ?? throw new LogicException('Core integration service has not been initialized.');
    }

    public function getDocumentsIntegrationService(): DocumentsIntegrationService
    {
        return $this->documentsIntegrationService ?? throw new LogicException('Documents integration service has not been initialized.');
    }

    public function getAnalyticsSourceService(): AnalyticsSourceService
    {
        return $this->analyticsSourceService ?? throw new LogicException('Analytics source service has not been initialized.');
    }

    public function getCrossProductIntegrationService(): CrossProductIntegrationService
    {
        return $this->crossProductIntegrationService ?? throw new LogicException('Cross-product integration service has not been initialized.');
    }
}
