<?php
/** Build installable Joomla component and package ZIPs. */
$root = dirname(__DIR__);
$dist = $root . '/dist';
$componentDir = $root . '/component';
$packageDir = $root . '/package';
$componentManifest = $componentDir . '/decaroprotocol.xml';
$packageManifest = $packageDir . '/pkg_decaroprotocol.xml';

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "ZipArchive non disponibile.\n");
    exit(1);
}

$componentXml = simplexml_load_file($componentManifest);
$packageXml = simplexml_load_file($packageManifest);

if ($componentXml === false || $packageXml === false) {
    fwrite(STDERR, "Impossibile leggere i manifest Joomla.\n");
    exit(1);
}

$componentVersion = trim((string) $componentXml->version);
$packageVersion = trim((string) $packageXml->version);

if ($componentVersion === '' || $componentVersion !== $packageVersion) {
    fwrite(STDERR, "Le versioni di componente e pacchetto non coincidono.\n");
    exit(1);
}

if (!preg_match('/^\d+\.\d+\.\d+(?:[-+][A-Za-z0-9.-]+)?$/', $componentVersion)) {
    fwrite(STDERR, "Versione non valida: {$componentVersion}\n");
    exit(1);
}

$version = $componentVersion;
@mkdir($dist, 0775, true);

function zipDirectory($source, $target)
{
    $zip = new ZipArchive();
    if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Impossibile creare {$target}");
    }
    $source = realpath($source);
    if ($source === false) {
        throw new RuntimeException('Directory sorgente non trovata.');
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $path = $file->getRealPath();
        $local = substr($path, strlen($source) + 1);
        $zip->addFile($path, str_replace(DIRECTORY_SEPARATOR, '/', $local));
    }
    $zip->close();
}

$componentZip = "{$dist}/com_decaroprotocol_{$version}.zip";
zipDirectory($componentDir, $componentZip);

$stage = sys_get_temp_dir() . '/pkg_decaroprotocol_' . bin2hex(random_bytes(4));
mkdir($stage . '/packages', 0775, true);
copy($packageManifest, $stage . '/pkg_decaroprotocol.xml');
copy($componentZip, $stage . "/packages/com_decaroprotocol_{$version}.zip");

$packageZip = "{$dist}/pkg_decaroprotocol_{$version}.zip";
zipDirectory($stage, $packageZip);

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $file) {
    $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
}
rmdir($stage);

echo basename($componentZip) . "\n" . basename($packageZip) . "\n";
