<?php
/**
 * @package     com_decaroprotocol
 * @subpackage  Administrator
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Xdecaro\Component\Decaroprotocol\Administrator\Extension\ProtocolComponent;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\AnalyticsSourceService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CoreIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CrossProductIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\DocumentsIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\ProtocolService;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Xdecaro\\Component\\Decaroprotocol'));
        $container->registerServiceProvider(new MVCFactory('\\Xdecaro\\Component\\Decaroprotocol'));

        $container->share(
            ProtocolService::class,
            static fn (Container $container): ProtocolService => new ProtocolService($container->get(DatabaseInterface::class))
        );
        $container->share(CoreIntegrationService::class, static fn (): CoreIntegrationService => new CoreIntegrationService());
        $container->share(
            DocumentsIntegrationService::class,
            static fn (Container $container): DocumentsIntegrationService => new DocumentsIntegrationService(
                $container->get(CoreIntegrationService::class),
                $container->get(DatabaseInterface::class)
            )
        );
        $container->share(
            AnalyticsSourceService::class,
            static fn (Container $container): AnalyticsSourceService => new AnalyticsSourceService(
                $container->get(DatabaseInterface::class)
            )
        );
        $container->share(
            CrossProductIntegrationService::class,
            static fn (): CrossProductIntegrationService => new CrossProductIntegrationService()
        );

        $container->set(
            ComponentInterface::class,
            static function (Container $container): ComponentInterface {
                $component = new ProtocolComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setProtocolService($container->get(ProtocolService::class));
                $component->setCoreIntegrationService($container->get(CoreIntegrationService::class));
                $component->setDocumentsIntegrationService($container->get(DocumentsIntegrationService::class));
                $component->setAnalyticsSourceService($container->get(AnalyticsSourceService::class));
                $component->setCrossProductIntegrationService($container->get(CrossProductIntegrationService::class));

                return $component;
            }
        );
    }
};
