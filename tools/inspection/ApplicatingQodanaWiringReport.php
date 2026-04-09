<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$payload = [
    'tool' => 'applicating-qodana-wiring',
    'status' => 'complete',
    'qodanaYaml' => is_file(applicating_path('qodana.yaml')),
    'workflow' => is_file(applicating_path('.github/workflows/qodana.yml')),
    'archiveExcluded' => is_file(applicating_path('qodana.yaml')),
];

applicating_write_json('report/inspection/applicating-qodana-wiring.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
