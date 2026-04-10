<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$report = [
    'tool' => 'application-fixture-sanity',
    'status' => 'complete',
    'environmentReady' => applicating_has_vendor_autoload(),
    'checks' => [ 'src/DataFixtures/ApplicationFixtures.php' => is_file(applicating_path('src/DataFixtures/ApplicationFixtures.php')), 'src/DataFixtures/ApplicationUserFixtures.php' => is_file(applicating_path('src/DataFixtures/ApplicationUserFixtures.php')) ],
];

applicating_write_json('report/inspection/application-fixture-sanity.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
