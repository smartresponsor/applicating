<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$directories = ['src', 'config', 'public', 'bin', 'tools'];
$files = applicating_scan_php_files($directories);
$issues = [];

foreach ($files as $file) {
    $output = [];
    $exitCode = 0;
    exec(sprintf('%s -l %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($file)), $output, $exitCode);
    if (0 !== $exitCode) {
        $issues[] = ['file' => str_replace(applicating_root() . '/', '', $file), 'output' => implode("
", $output)];
    }
}

$report = [
    'tool' => 'ApplicationPhpLint',
    'filesChecked' => count($files),
    'issues' => $issues,
    'status' => [] === $issues ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-php-lint.json', $report);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
