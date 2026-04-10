<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-doctrine-mapping-smoke',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'src/Entity' => is_dir(applicating_path('src/Entity')), 'migrations' => is_dir(applicating_path('migrations')), 'vendor/autoload.php' => applicating_has_vendor_autoload() ],
];

applicating_write_json('report/inspection/application-doctrine-mapping-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
