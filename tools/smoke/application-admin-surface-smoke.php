<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$checks = [
    'controller' => is_file(applicating_path('src/Controller/ApplicationAdminController.php')),
    'template_base' => is_file(applicating_path('templates/base.html.twig')),
    'security_config' => is_file(applicating_path('config/packages/application_security.yaml')),
    'admin_docs' => is_file(applicating_path('docs/ADMIN_AUTH_ROLES.md')),
    'tenant_entity' => is_file(applicating_path('src/Entity/TenantApplication.php')),
];

$report = [
    'tool' => 'application-admin-surface-smoke',
    'status' => [] === array_filter($checks, static fn (bool $value): bool => !$value) ? 'complete' : 'incomplete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => $checks,
];

applicating_write_json('report/inspection/application-admin-surface-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === array_filter($checks, static fn (bool $value): bool => !$value) ? 0 : 1);
