<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/catalog-route-inventory-report.json';

$routes = [];
$routeDir = $root . '/config/routes';
if (is_dir($routeDir)) {
    foreach (new DirectoryIterator($routeDir) as $file) {
        if ($file->isDot() || !$file->isFile()) {
            continue;
        }
        $routes[] = ['type' => 'yaml', 'file' => 'config/routes/' . $file->getFilename()];
    }
}
$controllerDir = $root . '/src/Controller';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerDir, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $contents = file_get_contents($file->getPathname()) ?: '';
    preg_match_all('/#\[Route\((.*?)\)\]/s', $contents, $matches);
    $routes[] = [
        'type' => 'attribute',
        'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname()),
        'count' => count($matches[0]),
    ];
}
file_put_contents($out, json_encode(['generatedAt' => date(DATE_ATOM), 'count' => count($routes), 'items' => $routes], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo sprintf("[CatalogRouteInventoryReport] %d route inventory rows written to %s\n", count($routes), str_replace($root . DIRECTORY_SEPARATOR, '', $out));
exit(0);
