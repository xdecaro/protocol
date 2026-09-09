<?php
namespace Xdecaro\Plugin\Xdecaroanalytics\Decaroprotocol\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Throwable;
use xdecaro\Component\Analytics\Administrator\Event\RegisterProvidersEvent;
use Xdecaro\Component\Decaroprotocol\Administrator\Extension\ProtocolComponent;
use Xdecaro\Plugin\Xdecaroanalytics\Decaroprotocol\Provider\ProtocolProvider;

final class Decaroprotocol extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [RegisterProvidersEvent::NAME => 'onRegisterProviders'];
    }

    public function onRegisterProviders(RegisterProvidersEvent $event): void
    {
        try {
            $component = Factory::getApplication()->bootComponent('com_decaroprotocol');
            if (!$component instanceof ProtocolComponent) {
                return;
            }

            $event->getRegistry()->register(new ProtocolProvider($component->getAnalyticsSourceService()));
        } catch (Throwable $exception) {
            Factory::getApplication()->getLogger()->warning(
                'Protocol Analytics provider was not registered: ' . $exception->getMessage(),
                ['category' => 'plg_xdecaroanalytics_decaroprotocol']
            );
        }
    }
}
