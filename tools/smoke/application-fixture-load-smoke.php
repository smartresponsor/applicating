<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-fixture-load-smoke',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'src/DataFixtures' => is_dir(applicating_path('src/DataFixtures')), 'vendor/autoload.php' => applicating_has_vendor_autoload() ],
];

applicating_write_json('report/inspection/application-fixture-load-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
