<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-container-boot-smoke',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'config/bootstrap.php' => is_file(applicating_path('config/bootstrap.php')), 'src/Kernel.php' => is_file(applicating_path('src/Kernel.php')), 'vendor/autoload.php' => applicating_has_vendor_autoload() ],
];

applicating_write_json('report/inspection/application-container-boot-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
