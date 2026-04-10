<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$payload = [
    'tool' => 'applicating-publish-guard',
    'status' => 'complete',
    'counts' => [
        'files' => count(applicating_scan_php_files(['src/Service/ApplicationPublishEligibilityService.php', 'src/Command/ApplicatingApplicationPublishCommand.php'])),
        'vendorReady' => applicating_has_vendor_autoload(),
    ],
];

applicating_write_json('report/inspection/applicating-publish-guard.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
