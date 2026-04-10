<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$workflowFiles = [
    '.github/workflows/qodana.yml',
    '.github/workflows/ci-smoke.yml',
    '.github/workflows/cd.yml',
    '.github/workflows/qa.yml',
    '.github/workflows/security-check.yml',
    '.github/workflows/build.yml',
    '.github/workflows/playwright.yml',
    '.github/workflows/test.yml',
    '.github/workflows/ci-precheck.yml',
];

$docsFiles = [
    'README.md',
    'docs/MAINTENANCE.md',
];

$issues = [];

foreach ($workflowFiles as $path) {
    $content = file_get_contents(applicating_path($path));
    if (false === $content) {
        $issues[] = sprintf('Unable to read workflow file: %s', $path);
        continue;
    }

    if (!str_contains($content, 'branches: [work]') && !str_contains($content, 'branches: [ work ]')) {
        $issues[] = sprintf('Workflow branch wiring is missing [work]: %s', $path);
    }

    if (str_contains($content, 'master')) {
        $issues[] = sprintf('Legacy branch token "master" found in workflow: %s', $path);
    }
}

foreach ($docsFiles as $path) {
    $content = file_get_contents(applicating_path($path));
    if (false === $content) {
        $issues[] = sprintf('Unable to read docs file: %s', $path);
        continue;
    }

    if (str_contains($content, 'pushes to `master`')) {
        $issues[] = sprintf('Legacy docs branch wording found in: %s', $path);
    }
}

$report = [
    'tool' => 'application-branch-wiring-smoke',
    'status' => [] === $issues ? 'complete' : 'failed',
    'issues' => $issues,
    'checkedWorkflows' => $workflowFiles,
    'checkedDocs' => $docsFiles,
];

applicating_write_json('report/inspection/application-branch-wiring-smoke.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
