<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-publish-guard-report.json';

$servicePath = $root . '/src/Service/ApplicationLifecycleService.php';
$testPaths = [
    $root . '/tests/Integration/ApplicationLifecycleServiceTest.php',
    $root . '/tests/Integration/ApplicationLifecyclePublishGuardTest.php',
];

$service = is_file($servicePath) ? (file_get_contents($servicePath) ?: '') : '';
$tests = '';
foreach ($testPaths as $testPath) {
    if (is_file($testPath)) {
        $tests .= file_get_contents($testPath) ?: '';
    }
}

$items = [
    [
        'name' => 'publish_checks_manifest_presence',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($service, 'getManifests()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'publish_checks_governance_state',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($service, 'getGovernanceState()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'publish_checks_release_state',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($service, 'getPublicationState()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'negative_publish_integration_tests',
        'path' => 'tests/Integration/ApplicationLifecycleServiceTest.php + tests/Integration/ApplicationLifecyclePublishGuardTest.php',
        'status' => (str_contains($tests, 'expectException') || str_contains($tests, 'cannot be published')) ? 'present' : 'missing',
    ],
];

file_put_contents(
    $out,
    json_encode([
        'generatedAt' => date(DATE_ATOM),
        'count' => count($items),
        'items' => $items,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo sprintf(
    "[ApplicatingPublishGuardReport] %d publish-guard rows written to %s\n",
    count($items),
    str_replace($root . DIRECTORY_SEPARATOR, '', $out)
);

exit(0);
