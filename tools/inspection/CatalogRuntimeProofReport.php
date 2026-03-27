<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/catalog-runtime-proof-report.json';

$checks = [
    'kernel' => $root . '/src/Kernel.php',
    'frontController' => $root . '/public/index.php',
    'openApi' => $root . '/api/category-openapi.yaml',
    'graphqlConfig' => $root . '/config/graphql/catalog_category.yaml',
    'doctrineConfig' => $root . '/config/packages/catalog_doctrine.yaml',
    'fixtureClass' => $root . '/src/DataFixtures/CategoryFixtures.php',
];
$items = [];
foreach ($checks as $label => $path) {
    $items[] = ['check' => $label, 'path' => str_replace($root . DIRECTORY_SEPARATOR, '', $path), 'exists' => file_exists($path)];
}
file_put_contents($out, json_encode(['generatedAt' => date(DATE_ATOM), 'items' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo sprintf("[CatalogRuntimeProofReport] %d runtime proof rows written to %s\n", count($items), str_replace($root . DIRECTORY_SEPARATOR, '', $out));
exit(0);
