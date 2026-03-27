<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$src = $root . '/src';
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/catalog-class-alias-report.json';

$byBase = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $base = $file->getBasename('.php');
    $byBase[$base][] = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
}
$duplicates = [];
foreach ($byBase as $base => $files) {
    if (count($files) > 1) {
        $duplicates[] = ['classBasename' => $base, 'files' => $files];
    }
}
file_put_contents($out, json_encode(['generatedAt' => date(DATE_ATOM), 'count' => count($duplicates), 'items' => $duplicates], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo sprintf("[CatalogClassAliasReport] %d duplicate class basenames written to %s\n", count($duplicates), str_replace($root . DIRECTORY_SEPARATOR, '', $out));
exit(0);
