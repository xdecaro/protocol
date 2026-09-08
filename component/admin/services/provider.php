<?php
/**
 * @package     com_decaroprotocol
 * @subpackage  Administrator
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\CoreIntegrationService;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\ProtocolService;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Xdecaro\\Component\\Decaroprotocol'));
        $container->registerServiceProvider(new MVCFactory('\\Xdecaro\\Component\\Decaroprotocol'));

        $container->share(
            ProtocolService::class,
            function (Container $container) {
                return new ProtocolService($container->get(DatabaseInterface::class));
            }
        );

        $container->share(
            CoreIntegrationService::class,
            static fn (): CoreIntegrationService => new CoreIntegrationService()
        );

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
