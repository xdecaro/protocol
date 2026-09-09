<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$serviceFile = $root . '/component/admin/src/Service/DocumentsIntegrationService.php';
$providerFile = $root . '/component/admin/services/provider.php';
$infoFile = $root . '/component/admin/src/Helper/InformationHelper.php';
$protocolFile = $root . '/component/admin/src/Service/ProtocolService.php';
$controllerFile = $root . '/component/admin/src/Controller/RecordController.php';
$viewFile = $root . '/component/admin/src/View/Record/HtmlView.php';
$templateFile = $root . '/component/admin/tmpl/record/edit.php';

foreach ([$serviceFile, $providerFile, $infoFile, $protocolFile, $controllerFile, $viewFile, $templateFile] as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Missing Protocol integration contract file: {$file}\n");
        exit(1);
    }
}

$service = (string) file_get_contents($serviceFile);
$provider = (string) file_get_contents($providerFile);
$info = (string) file_get_contents($infoFile);
$protocol = (string) file_get_contents($protocolFile);
$controller = (string) file_get_contents($controllerFile);
$view = (string) file_get_contents($viewFile);
$template = (string) file_get_contents($templateFile);
$runtime = implode("\n", [$service, $provider, $info, $protocol, $controller, $view, $template]);

$requiredServiceFragments = [
    "DOCUMENTS_COMPONENT = 'com_decarodocuments'",
    "MINIMUM_DOCUMENTS_VERSION = '1.2.1'",
    "PROTOCOL_COMPONENT = 'com_decaroprotocol'",
    "PROTOCOL_ENTITY = 'record'",
    "DEFAULT_RELATION_TYPE = 'attachment'",
    'bootComponent(self::DOCUMENTS_COMPONENT)',
    'getRelationService',
    '$this->assertDocumentsVersionCompatible()',
    "from(\$this->db->quoteName('#__extensions'))",
    '$this->assertProtocolPermission(\'core.manage\')',
    '$this->assertProtocolPermission(\'core.edit\')',
    'authorise($action, self::PROTOCOL_COMPONENT)',
    "new EntityReference(self::DOCUMENTS_COMPONENT, 'document', \$documentId)",
    '$this->assertRecordRelationsMutable($recordId)',
    "if (\$status !== 'draft')",
];

foreach ($requiredServiceFragments as $fragment) {
    if (!str_contains($service, $fragment)) {
        fwrite(STDERR, "Missing Documents integration contract fragment: {$fragment}\n");
        exit(1);
    }
}

$requiredControllerFragments = [
    "Session::checkToken('post')",
    'attachDocumentToRecord($documentId, $recordId)',
    'detachDocumentFromRecord($documentId, $recordId)',
    "getInt('record_id')",
    "getInt('document_id')",
];

foreach ($requiredControllerFragments as $fragment) {
    if (!str_contains($controller, $fragment)) {
        fwrite(STDERR, "Missing Protocol Documents controller guard: {$fragment}\n");
        exit(1);
    }
}

$requiredViewFragments = [
    'findDocumentsForRecord((int) $this->item->id)',
    "authorise('core.manage', DocumentsIntegrationService::DOCUMENTS_COMPONENT)",
    "authorise('core.edit', DocumentsIntegrationService::DOCUMENTS_COMPONENT)",
];

foreach ($requiredViewFragments as $fragment) {
    if (!str_contains($view, $fragment)) {
        fwrite(STDERR, "Missing Protocol Documents view contract: {$fragment}\n");
        exit(1);
    }
}

$requiredTemplateFragments = [
    'task=record.attachDocument',
    'task=record.detachDocument',
    'task=document.download',
    "HTMLHelper::_('form.token')",
    'COM_DECAROPROTOCOL_DOCUMENTS_IMMUTABLE',
];

foreach ($requiredTemplateFragments as $fragment) {
    if (!str_contains($template, $fragment)) {
        fwrite(STDERR, "Missing Protocol Documents UI contract: {$fragment}\n");
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

fwrite(STDOUT, "Protocol/Documents functional integration contract OK\n");
