<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$mainControllerPath = applicating_path('src/Controller/ApplicationAdminController.php');
$lifecycleControllerPath = applicating_path('src/Controller/ApplicationAdminLifecycleController.php');

$issues = [];

$mainControllerCode = file_get_contents($mainControllerPath);
$lifecycleControllerCode = file_get_contents($lifecycleControllerPath);

if (false === $mainControllerCode) {
    $issues[] = 'Unable to read src/Controller/ApplicationAdminController.php';
}

if (false === $lifecycleControllerCode) {
    $issues[] = 'Unable to read src/Controller/ApplicationAdminLifecycleController.php';
}

if (false !== $mainControllerCode) {
    foreach (['createRelease', 'createManifest', 'publish', 'suspend', 'assign', 'toggle'] as $method) {
        if (str_contains($mainControllerCode, sprintf('function %s(', $method))) {
            $issues[] = sprintf('Method %s still exists in ApplicationAdminController.', $method);
        }
    }
}

if (false !== $lifecycleControllerCode) {
    foreach (['createRelease', 'createManifest', 'publish', 'suspend', 'assign', 'toggle'] as $method) {
        if (!str_contains($lifecycleControllerCode, sprintf('function %s(', $method))) {
            $issues[] = sprintf('Method %s is missing in ApplicationAdminLifecycleController.', $method);
        }
    }
}

$report = [
    'tool' => 'application-admin-controller-decomposition-smoke',
    'status' => [] === $issues ? 'complete' : 'failed',
    'issues' => $issues,
];

applicating_write_json('report/inspection/application-admin-controller-decomposition-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
