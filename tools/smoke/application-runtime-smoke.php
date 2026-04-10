<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-runtime-smoke',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'public/index.php' => is_file(applicating_path('public/index.php')), 'src/Kernel.php' => is_file(applicating_path('src/Kernel.php')), 'vendor/autoload.php' => applicating_has_vendor_autoload() ],
];

applicating_write_json('report/inspection/application-runtime-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
