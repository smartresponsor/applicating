<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$configFiles = applicating_collect_directory_listing('config');
$issues = [];
foreach ($configFiles as $file) {
    if (!str_starts_with($file, 'applicating_') && !in_array($file, ['bootstrap.php', 'bundles.php', 'applicating_reference.php'], true)) {
        $issues[] = $file;
    }
}

$report = [
    'tool' => 'applicating_config_prefix_check',
    'files' => $configFiles,
    'issues' => $issues,
    'status' => [] === $issues ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-config-prefix-check.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
