<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Xdecaro\Plugin\Xdecaroanalytics\Decaroprotocol\Extension\Decaroprotocol;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            $container->lazy(Decaroprotocol::class, static function (): Decaroprotocol {
                return new Decaroprotocol((array) PluginHelper::getPlugin('xdecaroanalytics', 'decaroprotocol'));
            })
        );
    }
};
