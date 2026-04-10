<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-postgres-matrix-smoke',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'config/packages/applicating_doctrine.yaml' => is_file(applicating_path('config/packages/applicating_doctrine.yaml')), 'migrations' => is_dir(applicating_path('migrations')) ],
];

applicating_write_json('report/inspection/application-postgres-matrix-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
