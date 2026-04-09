<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$files = applicating_scan_php_files(['src']);
$issues = applicating_detect_namespace_issues($files);
$report = [
    'tool' => 'app_namespace_check',
    'checked' => count($files),
    'issues' => $issues,
    'status' => [] === $issues ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/app-namespace-check.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
