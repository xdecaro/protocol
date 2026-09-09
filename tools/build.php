<?php
/** Build deterministic installable Joomla component, plugin and package ZIPs. */
$root = dirname(__DIR__);
$dist = $root . '/dist';
$componentDir = $root . '/component';
$pluginDir = $root . '/plugins/xdecaroanalytics/decaroprotocol';
$componentManifest = $componentDir . '/decaroprotocol.xml';
$pluginManifest = $pluginDir . '/decaroprotocol.xml';
$packageManifest = $root . '/package/pkg_decaroprotocol.xml';
$packageScript = $root . '/package/script.php';
$fixedTime = gmmktime(0, 0, 0, 1, 1, 2026);

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "ZipArchive non disponibile.\n");
    exit(1);
}

$componentXml = simplexml_load_file($componentManifest);
$pluginXml = simplexml_load_file($pluginManifest);
$packageXml = simplexml_load_file($packageManifest);
if ($componentXml === false || $pluginXml === false || $packageXml === false) {
    fwrite(STDERR, "Impossibile leggere i manifest Joomla.\n");
    exit(1);
}

$versions = [trim((string) $componentXml->version), trim((string) $pluginXml->version), trim((string) $packageXml->version)];
if ($versions[0] === '' || count(array_unique($versions)) !== 1) {
    fwrite(STDERR, "Le versioni degli artefatti Protocol non coincidono.\n");
    exit(1);
}
$version = $versions[0];
if (!preg_match('/^\d+\.\d+\.\d+(?:[-+][A-Za-z0-9.-]+)?$/', $version)) {
    fwrite(STDERR, "Versione non valida: {$version}\n");
    exit(1);
}

if (is_dir($dist)) {
    foreach (glob($dist . '/*') ?: [] as $file) {
        if (is_file($file)) { unlink($file); }
    }
} else {
    mkdir($dist, 0775, true);
}

function addDeterministicEntry(ZipArchive $zip, string $name, string $data, int $fixedTime): void
{
    if (!$zip->addFromString($name, $data)) {
        throw new RuntimeException("Impossibile aggiungere {$name} allo ZIP.");
    }
    if (method_exists($zip, 'setMtimeName')) { $zip->setMtimeName($name, $fixedTime); }
    if (method_exists($zip, 'setExternalAttributesName')) {
        $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16);
    }
}

function zipDirectoryDeterministic(string $source, string $target, int $fixedTime): void
{
    $source = realpath($source);
    if ($source === false) { throw new RuntimeException('Directory sorgente non trovata.'); }
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) { continue; }
        $path = $file->getRealPath();
        if ($path === false) { continue; }
        $local = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($source) + 1));
        $files[$local] = $path;
    }
    ksort($files, SORT_STRING);
    $zip = new ZipArchive();
    if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Impossibile creare {$target}");
    }
    foreach ($files as $local => $path) {
        addDeterministicEntry($zip, $local, (string) file_get_contents($path), $fixedTime);
    }
    $zip->close();
}

$componentZip = "{$dist}/com_decaroprotocol_{$version}.zip";
$pluginZip = "{$dist}/plg_xdecaroanalytics_decaroprotocol_{$version}.zip";
zipDirectoryDeterministic($componentDir, $componentZip, $fixedTime);
zipDirectoryDeterministic($pluginDir, $pluginZip, $fixedTime);

$packageZip = "{$dist}/pkg_decaroprotocol_{$version}.zip";
$zip = new ZipArchive();
if ($zip->open($packageZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException("Impossibile creare {$packageZip}");
}
addDeterministicEntry($zip, 'pkg_decaroprotocol.xml', (string) file_get_contents($packageManifest), $fixedTime);
addDeterministicEntry($zip, 'script.php', (string) file_get_contents($packageScript), $fixedTime);
addDeterministicEntry($zip, "packages/com_decaroprotocol_{$version}.zip", (string) file_get_contents($componentZip), $fixedTime);
addDeterministicEntry($zip, "packages/plg_xdecaroanalytics_decaroprotocol_{$version}.zip", (string) file_get_contents($pluginZip), $fixedTime);
$zip->close();

$checksums = '';
foreach ([$componentZip, $pluginZip, $packageZip] as $file) {
    $checksums .= hash_file('sha256', $file) . '  ' . basename($file) . "\n";
}
file_put_contents($dist . '/SHA256SUMS.txt', $checksums);

echo basename($componentZip) . "\n" . basename($pluginZip) . "\n" . basename($packageZip) . "\n";
