<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-engineering-drift-report.json';

$checks = [
    [
        'name' => 'composer_report_wiring',
        'path' => $root . '/composer.json',
        'markers' => [
            'tools/inspection/CatalogOwnerOverlapReport.php',
            'tools/inspection/CatalogRouteInventoryReport.php',
            'tools/inspection/CatalogClassAliasReport.php',
            'tools/inspection/CatalogRuntimeProofReport.php',
        ],
    ],
    [
        'name' => 'docs_changelog_product_suite',
        'path' => $root . '/docs/CHANGELOG.md',
        'markers' => [
            'Smartresponsor Product Suite',
            'Product entity',
            'Catalog adapter',
        ],
    ],
    [
        'name' => 'docs_release_product_suite',
        'path' => $root . '/docs/RELEASE.md',
        'markers' => [
            'Smartresponsor Product Suite',
            'product-suite-final-cut.zip',
            'product-suite-roadmap.zip',
        ],
    ],
    [
        'name' => 'tmp_write_check_file',
        'path' => $root . '/tmp-chatgpt-write-check.txt',
        'markers' => [],
    ],
];

$items = [];
foreach ($checks as $check) {
    $exists = file_exists($check['path']);
    $content = $exists && is_file($check['path']) ? (file_get_contents($check['path']) ?: '') : '';
    $hits = [];

    foreach ($check['markers'] as $marker) {
        if (str_contains($content, $marker)) {
            $hits[] = $marker;
        }
    }

    $items[] = [
        'name' => $check['name'],
        'path' => str_replace($root . DIRECTORY_SEPARATOR, '', $check['path']),
        'exists' => $exists,
        'markerHits' => $hits,
        'status' => [] === $check['markers'] ? ($exists ? 'present' : 'missing') : ([] === $hits ? 'clean' : 'drift'),
    ];
}

file_put_contents(
    $out,
    json_encode([
        'generatedAt' => date(DATE_ATOM),
        'count' => count($items),
        'items' => $items,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo sprintf(
    "[ApplicatingEngineeringDriftReport] %d engineering drift rows written to %s\n",
    count($items),
    str_replace($root . DIRECTORY_SEPARATOR, '', $out)
);

exit(0);
