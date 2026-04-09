<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$checks = [
    'public_index' => is_file(applicating_path('public/index.php')),
    'kernel' => is_file(applicating_path('src/Kernel.php')),
    'health_controller' => is_file(applicating_path('src/Controller/ApplicationHealthController.php')),
    'readiness_controller' => is_file(applicating_path('src/Controller/ApplicationReadinessApiController.php')),
    'login_controller' => is_file(applicating_path('src/Controller/SecurityController.php')),
    'admin_controller' => is_file(applicating_path('src/Controller/ApplicationAdminController.php')),
    'security_config' => is_file(applicating_path('config/packages/applicating_security.yaml')),
    'operations_docs' => is_file(applicating_path('docs/OPERATIONS.md')),
];

$report = [
    'tool' => 'application-functional-readiness-smoke',
    'status' => [] === array_filter($checks, static fn (bool $value): bool => !$value) ? 'complete' : 'incomplete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => $checks,
];

applicating_write_json('report/inspection/application-functional-readiness-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === array_filter($checks, static fn (bool $value): bool => !$value) ? 0 : 1);
