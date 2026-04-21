<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$servicesPath = applicating_path('config/services/applicating_services.yaml');
$aliases = [];
if (is_file($servicesPath)) {
    $lines = applicating_read_lines($servicesPath);
    foreach ($lines as $line) {
        if (preg_match('/^\s{4}(App\\Application\\ServiceInterface\\[^:]+):$/', $line, $m)) {
            $aliases[] = $m[1];
        }
    }
}

$payload = [
    'tool' => 'applicating-class-alias',
    'status' => 'complete',
    'aliases' => $aliases,
    'count' => count($aliases),
];

applicating_write_json('report/inspection/applicating-class-alias.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit(0);
