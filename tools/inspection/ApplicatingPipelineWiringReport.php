<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$pipeline = applicating_path('.github/workflows/ci-smoke.yml');
$content = is_file($pipeline) ? file_get_contents($pipeline) : '';
$checks = [
    'ci_smoke' => is_file($pipeline),
    'qa_yaml' => is_file(applicating_path('.github/workflows/qa.yml')),
    'admin_smoke' => false !== strpos((string) $content, 'composer smoke:admin'),
    'functional_readiness_smoke' => false !== strpos((string) $content, 'composer smoke:functional-readiness'),
];

$payload = [
    'tool' => 'applicating-pipeline-wiring',
    'status' => [] === array_filter($checks, static fn (bool $value): bool => !$value) ? 'complete' : 'incomplete',
    'checks' => $checks,
];

applicating_write_json('report/inspection/applicating-pipeline-wiring.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === array_filter($checks, static fn (bool $value): bool => !$value) ? 0 : 1);
