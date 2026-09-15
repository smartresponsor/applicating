<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$forbidden = ['Catalog', 'Cataloging', 'Automation', 'Automating', 'Applicating'];
$issues = [];
foreach (['.', 'src'] as $rootDir) {
    $entries = applicating_collect_directory_listing($rootDir);
    foreach ($entries as $entry) {
        if (in_array($entry, $forbidden, true)) {
            $issues[] = sprintf('%s/%s', $rootDir, $entry);
        }
    }
}

$forbiddenPaths = [
    'src/DTO/Application',
    'src/Form/Application',
    'src/Value',
];
foreach ($forbiddenPaths as $forbiddenPath) {
    if (is_dir($forbiddenPath)) {
        $issues[] = $forbiddenPath;
    }
}

foreach (glob('src/DTO/*.php') ?: [] as $dtoFile) {
    if (!str_ends_with(basename($dtoFile), 'DTO.php')) {
        $issues[] = $dtoFile;
    }
}

$report = [
    'tool' => 'applicating_canonical_roots_check',
    'issues' => $issues,
    'status' => [] === $issues ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-canonical-roots-check.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
