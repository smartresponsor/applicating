<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$qodanaYamlPath = applicating_path('qodana.yaml');
$qodanaYaml = is_file($qodanaYamlPath) ? (string) file_get_contents($qodanaYamlPath) : '';
$payload = [
    'tool' => 'applicating-qodana-wiring',
    'status' => 'complete',
    'qodanaYaml' => is_file($qodanaYamlPath),
    'workflow' => is_file(applicating_path('.github/workflows/qodana.yml')),
    'archiveExcluded' => str_contains($qodanaYaml, 'archive/repo-drift')
        && str_contains($qodanaYaml, 'report')
        && str_contains($qodanaYaml, 'var')
        && str_contains($qodanaYaml, '.phpunit.cache')
        && str_contains($qodanaYaml, '.idea'),
];

applicating_write_json('report/inspection/applicating-qodana-wiring.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
