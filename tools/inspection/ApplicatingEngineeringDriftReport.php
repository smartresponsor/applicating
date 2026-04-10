<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$payload = [
    'tool' => 'applicating-engineering-drift',
    'status' => 'complete',
    'counts' => [
        'files' => count(applicating_scan_php_files(['archive/repo-drift', 'docs', 'src'])),
        'vendorReady' => applicating_has_vendor_autoload(),
    ],
];

applicating_write_json('report/inspection/applicating-engineering-drift.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
