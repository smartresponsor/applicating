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
            'tools/inspection/ApplicatingEngineeringDriftReport.php',
            'tools/inspection/ApplicatingPipelineWiringReport.php',
            'tools/inspection/ApplicatingPublishGuardReport.php',
            'tools/inspection/ApplicatingFixturePublishGuardReport.php',
        ],
    ],
    [
        'name' => 'local_pipeline_include_reports_steps',
        'path' => $root . '/tools/ci/run-application-local-pipeline.ps1',
        'requiredMarkers' => [
            'composer report:owner-overlap',
            'composer report:route-inventory',
            'composer report:class-alias',
            'composer report:runtime-proof',
            'composer report:engineering-drift',
            'composer report:pipeline-wiring',
            'composer report:publish-guard',
            'composer report:fixture-publish-guard',
        ],
    ],
    [
        'name' => 'applicating_wave_runner_has_extended_reports',
        'path' => $root . '/tools/ci/run-applicating-inspection-wave.ps1',
        'requiredMarkers' => [
            'ApplicatingEngineeringDriftReport.php',
            'ApplicatingPipelineWiringReport.php',
            'ApplicatingPublishGuardReport.php',
            'ApplicatingFixturePublishGuardReport.php',
        ],
    ],
    [
        'name' => 'legacy_category_linter_present',
        'path' => $root . '/tools/linter/category_canonical_roots_check.php',
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
