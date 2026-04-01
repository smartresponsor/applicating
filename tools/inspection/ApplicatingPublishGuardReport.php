<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-publish-guard-report.json';

$lifecycleServicePath = $root . '/src/Service/ApplicationLifecycleService.php';
$eligibilityServicePath = $root . '/src/Service/ApplicationPublishEligibilityService.php';
$integrationTestPaths = [
    $root . '/tests/Integration/ApplicationLifecycleServiceTest.php',
    $root . '/tests/Integration/ApplicationLifecyclePublishGuardTest.php',
    $root . '/tests/Integration/ApplicationPublishEligibilityServiceTest.php',
    $root . '/tests/Integration/ApplicationPublishEligibilityServiceContainerTest.php',
];
$functionalTestPaths = [
    $root . '/tests/Functional/ApplicationPublishGracefulHandlingTest.php',
    $root . '/tests/Functional/ApplicatingApplicationPublishCommandFailureTest.php',
    $root . '/tests/Functional/ApplicationPublishEligibilityViewTest.php',
];

$lifecycleService = is_file($lifecycleServicePath) ? (file_get_contents($lifecycleServicePath) ?: '') : '';
$eligibilityService = is_file($eligibilityServicePath) ? (file_get_contents($eligibilityServicePath) ?: '') : '';
$integrationTests = '';
foreach ($integrationTestPaths as $testPath) {
    if (is_file($testPath)) {
        $integrationTests .= file_get_contents($testPath) ?: '';
    }
}
$functionalTests = '';
foreach ($functionalTestPaths as $testPath) {
    if (is_file($testPath)) {
        $functionalTests .= file_get_contents($testPath) ?: '';
    }
}

$items = [
    [
        'name' => 'publish_checks_manifest_presence',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($lifecycleService, 'getManifests()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'publish_checks_governance_state',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($lifecycleService, 'getGovernanceState()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'publish_checks_release_state',
        'path' => 'src/Service/ApplicationLifecycleService.php',
        'status' => str_contains($lifecycleService, 'getPublicationState()') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'publish_eligibility_service_exists',
        'path' => 'src/Service/ApplicationPublishEligibilityService.php',
        'status' => str_contains($eligibilityService, 'buildEligibilityMap') ? 'present' : 'missing',
    ],
    [
        'name' => 'publish_eligibility_service_checks_published_release',
        'path' => 'src/Service/ApplicationPublishEligibilityService.php',
        'status' => str_contains($eligibilityService, 'Release is already published.') ? 'guarded' : 'missing',
    ],
    [
        'name' => 'negative_publish_integration_tests',
        'path' => 'tests/Integration/ApplicationLifecycleServiceTest.php + tests/Integration/ApplicationLifecyclePublishGuardTest.php',
        'status' => (str_contains($integrationTests, 'cannot be published without a manifest') || str_contains($integrationTests, 'Application cannot be published without a manifest.')) ? 'present' : 'missing',
    ],
    [
        'name' => 'publish_eligibility_service_integration_tests',
        'path' => 'tests/Integration/ApplicationPublishEligibilityServiceTest.php',
        'status' => str_contains($integrationTests, 'Release is already published.') ? 'present' : 'missing',
    ],
    [
        'name' => 'publish_eligibility_container_wiring',
        'path' => 'tests/Integration/ApplicationPublishEligibilityServiceContainerTest.php',
        'status' => str_contains($integrationTests, 'ApplicationPublishEligibilityServiceInterface::class') ? 'present' : 'missing',
    ],
    [
        'name' => 'admin_publish_failure_functional_tests',
        'path' => 'tests/Functional/ApplicationPublishGracefulHandlingTest.php',
        'status' => str_contains($functionalTests, 'Application cannot be published without an approved manifest.') ? 'present' : 'missing',
    ],
    [
        'name' => 'cli_publish_failure_functional_tests',
        'path' => 'tests/Functional/ApplicatingApplicationPublishCommandFailureTest.php',
        'status' => str_contains($functionalTests, 'applicating:application:publish') ? 'present' : 'missing',
    ],
    [
        'name' => 'publish_eligibility_view_functional_tests',
        'path' => 'tests/Functional/ApplicationPublishEligibilityViewTest.php',
        'status' => str_contains($functionalTests, 'Publish requires an attached manifest.') ? 'present' : 'missing',
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
