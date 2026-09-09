<?php
define('_JEXEC', 1);

$root = dirname(__DIR__);
$required = [
    'component/admin/src/Extension/ProtocolComponent.php',
    'component/admin/src/Service/CoreIntegrationService.php',
    'component/admin/src/Service/AnalyticsSourceService.php',
    'component/admin/src/Service/CrossProductIntegrationService.php',
    'plugins/xdecaroanalytics/decaroprotocol/src/Extension/Decaroprotocol.php',
    'plugins/xdecaroanalytics/decaroprotocol/src/Provider/ProtocolProvider.php',
];
foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        throw new RuntimeException('Missing integration file: ' . $file);
    }
}

$core = (string) file_get_contents($root . '/component/admin/src/Service/CoreIntegrationService.php');
foreach (['CapabilityRegistry', 'protocol.records', 'protocol.protocolize', 'protocol.query', 'protocol.documents', 'protocol.analytics.provider', 'protocol.notifications.bridge', 'protocol.tasks.bridge'] as $needle) {
    if (!str_contains($core, $needle)) {
        throw new RuntimeException('Missing Core integration contract: ' . $needle);
    }
}
if (str_contains($core, 'Xdecaro\\Core')) {
    throw new RuntimeException('Deprecated Core namespace detected.');
}

$bridge = (string) file_get_contents($root . '/component/admin/src/Service/CrossProductIntegrationService.php');
foreach (['bootComponent', 'com_xdecaronotifications', 'getNotificationService', 'com_xdecarotasks', 'getTaskService'] as $needle) {
    if (!str_contains($bridge, $needle)) {
        throw new RuntimeException('Missing public bridge contract: ' . $needle);
    }
}
foreach (['#__xdecaronotifications_', '#__xdecarotasks_'] as $forbidden) {
    if (str_contains($bridge, $forbidden)) {
        throw new RuntimeException('Private cross-product table access detected: ' . $forbidden);
    }
}

$analytics = (string) file_get_contents($root . '/component/admin/src/Service/AnalyticsSourceService.php');
if (!str_contains($analytics, '#__decaroprotocol_')) {
    throw new RuntimeException('Protocol analytics source does not read Protocol-owned data.');
}
foreach (['#__decarodocuments_', '#__xdecaronotifications_', '#__xdecarotasks_', '#__xdecaroanalytics_'] as $forbidden) {
    if (str_contains($analytics, $forbidden)) {
        throw new RuntimeException('Analytics source crossed a private product boundary: ' . $forbidden);
    }
}

$component = (string) file_get_contents($root . '/component/admin/src/Extension/ProtocolComponent.php');
foreach (['getProtocolService', 'getDocumentsIntegrationService', 'getAnalyticsSourceService', 'getCrossProductIntegrationService'] as $method) {
    if (!str_contains($component, $method)) {
        throw new RuntimeException('Missing Protocol component facade method: ' . $method);
    }
}

echo "Protocol public integration contract OK\n";
