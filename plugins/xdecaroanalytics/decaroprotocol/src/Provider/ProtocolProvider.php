<?php
namespace Xdecaro\Plugin\Xdecaroanalytics\Decaroprotocol\Provider;

defined('_JEXEC') or die;

use xdecaro\Component\Analytics\Administrator\Contract\AnalyticsProviderInterface;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\AnalyticsSourceService;

final class ProtocolProvider implements AnalyticsProviderInterface
{
    public function __construct(private AnalyticsSourceService $source)
    {
    }

    public function getKey(): string { return 'protocol'; }
    public function getLabel(): string { return 'Protocol'; }
    public function getMetrics(): array { return $this->source->getMetrics(); }
    public function getDatasets(): array { return $this->source->getDatasets(); }
    public function getMetric(string $metricKey, array $context = []): array { return $this->source->getMetric($metricKey, $context); }
    public function getDataset(string $datasetKey, array $context = []): array { return $this->source->getDataset($datasetKey, $context); }
}
