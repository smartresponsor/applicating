<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-pipeline-wiring-report.json';

$checks = [
    [
        'name' => 'composer_report_commands',
        'path' => $root . '/composer.json',
        'requiredMarkers' => [
            'tools/inspection/ApplicatingOwnerOverlapReport.php',
            'tools/inspection/ApplicatingRouteInventoryReport.php',
            'tools/inspection/ApplicatingClassAliasReport.php',
            'tools/inspection/ApplicatingRuntimeProofReport.php',
        ],
    ],
    [
        'name' => 'pipeline_runner_has_applicating_wave',
        'path' => $root . '/tools/ci/run-application-local-pipeline.ps1',
        'requiredMarkers' => [
            'applicating_canonical_roots_check.php',
            'ApplicatingOwnerOverlapReport.php',
            'ApplicatingEngineeringDriftReport.php',
        ],
    ],
    [
        'name' => 'legacy_category_linter_present',
        'path' => $root . '/tools/linter/category_canonical_roots_check.php',
        'requiredMarkers' => [],
    ],
    [
        'name' => 'applicating_wave_runner_present',
        'path' => $root . '/tools/ci/run-applicating-inspection-wave.ps1',
        'requiredMarkers' => [],
    ],
];

$items = [];
foreach ($checks as $check) {
    $exists = file_exists($check['path']);
    $content = $exists && is_file($check['path']) ? (file_get_contents($check['path']) ?: '') : '';
    $hits = [];
    foreach ($check['requiredMarkers'] as $marker) {
        if (str_contains($content, $marker)) {
            $hits[] = $marker;
        }
    }

    $status = [] === $check['requiredMarkers']
        ? ($exists ? 'present' : 'missing')
        : (count($hits) === count($check['requiredMarkers']) ? 'wired' : 'drift');

    $items[] = [
        'name' => $check['name'],
        'path' => str_replace($root . DIRECTORY_SEPARATOR, '', $check['path']),
        'exists' => $exists,
        'markerHits' => $hits,
        'expectedMarkerCount' => count($check['requiredMarkers']),
        'status' => $status,
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
    "[ApplicatingPipelineWiringReport] %d pipeline wiring rows written to %s\n",
    count($items),
    str_replace($root . DIRECTORY_SEPARATOR, '', $out)
);

exit(0);
