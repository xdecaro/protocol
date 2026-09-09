<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$serviceFile = $root . '/component/admin/src/Service/DocumentsIntegrationService.php';
$providerFile = $root . '/component/admin/services/provider.php';
$infoFile = $root . '/component/admin/src/Helper/InformationHelper.php';
$protocolFile = $root . '/component/admin/src/Service/ProtocolService.php';

foreach ([$serviceFile, $providerFile, $infoFile, $protocolFile] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing Protocol integration contract file: {$file}\n");
        exit(1);
    }
}

$service = (string) file_get_contents($serviceFile);
$provider = (string) file_get_contents($providerFile);
$info = (string) file_get_contents($infoFile);
$protocol = (string) file_get_contents($protocolFile);
$runtime = $service . "\n" . $provider . "\n" . $info . "\n" . $protocol;

$requiredServiceFragments = [
    "DOCUMENTS_COMPONENT = 'com_decarodocuments'",
    "PROTOCOL_COMPONENT = 'com_decaroprotocol'",
    "PROTOCOL_ENTITY = 'record'",
    "DEFAULT_RELATION_TYPE = 'attachment'",
    'bootComponent(self::DOCUMENTS_COMPONENT)',
    'getRelationService',
    '$this->assertProtocolPermission(\'core.manage\')',
    '$this->assertProtocolPermission(\'core.edit\')',
    'authorise($action, self::PROTOCOL_COMPONENT)',
    "new EntityReference(self::DOCUMENTS_COMPONENT, 'document', \$documentId)",
];

foreach ($requiredServiceFragments as $fragment) {
    if (!str_contains($service, $fragment)) {
        fwrite(STDERR, "Missing Documents integration contract fragment: {$fragment}\n");
        exit(1);
    }
}

if (str_contains($runtime, '#__decarodocuments_')) {
    fwrite(STDERR, "Protocol must not access private Documents tables.\n");
    exit(1);
}

if (!str_contains($provider, 'DocumentsIntegrationService::class')) {
    fwrite(STDERR, "DocumentsIntegrationService is not registered in Protocol DI.\n");
    exit(1);
}

if (!str_contains($info, "'Competitions' => 'com_xdecarocompetitions'")) {
    fwrite(STDERR, "Protocol diagnostics do not use the current Competitions component ID.\n");
    exit(1);
}

if (str_contains($info, 'com_decarodcl')) {
    fwrite(STDERR, "Obsolete Competitions component ID remains in Protocol diagnostics.\n");
    exit(1);
}

if (str_contains($runtime, 'Xdecaro\\Core')) {
    fwrite(STDERR, "Deprecated Core namespace detected in Protocol runtime integration.\n");
    exit(1);
}

fwrite(STDOUT, "Protocol/Documents integration contract OK\n");
