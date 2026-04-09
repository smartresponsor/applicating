<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$requiredExtensions = ['mbstring', 'simplexml', 'xml', 'dom', 'xmlwriter', 'json', 'libxml', 'tokenizer', 'sqlite3', 'pdo_sqlite'];
$missingExtensions = [];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

$requiredPaths = ['composer.json', 'phpunit.xml.dist', 'config/bootstrap.php', 'src/Kernel.php'];
$missingPaths = [];
foreach ($requiredPaths as $relativePath) {
    if (!is_file(applicating_path($relativePath))) {
        $missingPaths[] = $relativePath;
    }
}

$report = [
    'tool' => 'ApplicationQaEnvironmentCheck',
    'phpVersion' => PHP_VERSION,
    'requiredExtensions' => $requiredExtensions,
    'missingExtensions' => $missingExtensions,
    'requiredPaths' => $requiredPaths,
    'missingPaths' => $missingPaths,
    'vendorReady' => applicating_has_vendor_autoload(),
    'status' => ([] === $missingExtensions && [] === $missingPaths) ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-qa-environment-check.json', $report);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $missingExtensions && [] === $missingPaths ? 0 : 1);
