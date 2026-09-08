<?php
/** Build deterministic installable Joomla component and package ZIPs. */
$root = dirname(__DIR__);
$dist = $root . '/dist';
$componentDir = $root . '/component';
$componentManifest = $componentDir . '/decaroprotocol.xml';
$packageManifest = $root . '/package/pkg_decaroprotocol.xml';
$fixedTime = gmmktime(0, 0, 0, 1, 1, 2026);

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
if (is_dir($dist)) {
    foreach (glob($dist . '/*') ?: [] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
} else {
    mkdir($dist, 0775, true);
}

function addDeterministicEntry(ZipArchive $zip, string $name, string $data, int $fixedTime): void
{
    if (!$zip->addFromString($name, $data)) {
        throw new RuntimeException("Impossibile aggiungere {$name} allo ZIP.");
    }

    if (method_exists($zip, 'setMtimeName')) {
        $zip->setMtimeName($name, $fixedTime);
    }

    if (method_exists($zip, 'setExternalAttributesName')) {
        $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16);
    }
}

function zipDirectoryDeterministic(string $source, string $target, int $fixedTime): void
{
    $source = realpath($source);
    if ($source === false) {
        throw new RuntimeException('Directory sorgente non trovata.');
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $path = $file->getRealPath();
            if ($path !== false) {
                $local = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($source) + 1));
                $files[$local] = $path;
            }
        }
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
zipDirectoryDeterministic($componentDir, $componentZip, $fixedTime);

$packageZip = "{$dist}/pkg_decaroprotocol_{$version}.zip";
$zip = new ZipArchive();
if ($zip->open($packageZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    throw new RuntimeException("Impossibile creare {$packageZip}");
}
addDeterministicEntry($zip, 'pkg_decaroprotocol.xml', (string) file_get_contents($packageManifest), $fixedTime);
addDeterministicEntry(
    $zip,
    "packages/com_decaroprotocol_{$version}.zip",
    (string) file_get_contents($componentZip),
    $fixedTime
);
$zip->close();

$checksums = '';
foreach ([$componentZip, $packageZip] as $file) {
    $checksums .= hash_file('sha256', $file) . '  ' . basename($file) . "\n";
}
file_put_contents($dist . '/SHA256SUMS.txt', $checksums);

echo basename($componentZip) . "\n" . basename($packageZip) . "\n";
