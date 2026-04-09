<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$checks = [
    'runtime' => is_file(applicating_path('tools/smoke/application-runtime-smoke.php')),
    'container' => is_file(applicating_path('tools/smoke/application-container-boot-smoke.php')),
    'doctrine' => is_file(applicating_path('tools/smoke/application-doctrine-mapping-smoke.php')),
    'fixture_load' => is_file(applicating_path('tools/smoke/application-fixture-load-smoke.php')),
    'admin' => is_file(applicating_path('tools/smoke/application-admin-surface-smoke.php')),
    'functional_readiness' => is_file(applicating_path('tools/smoke/application-functional-readiness-smoke.php')),
];

$payload = [
    'tool' => 'applicating-runtime-proof',
    'status' => [] === array_filter($checks, static fn (bool $value): bool => !$value) ? 'complete' : 'incomplete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => $checks,
];

applicating_write_json('report/inspection/applicating-runtime-proof.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === array_filter($checks, static fn (bool $value): bool => !$value) ? 0 : 1);
