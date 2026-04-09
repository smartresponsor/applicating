<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$pipeline = applicating_path('.github/workflows/ci-smoke.yml');
$pipelineContent = is_file($pipeline) ? (string) file_get_contents($pipeline) : '';
$localPipeline = applicating_path('tools/ci/run-application-local-pipeline.ps1');
$localPipelineContent = is_file($localPipeline) ? (string) file_get_contents($localPipeline) : '';
$composer = applicating_path('composer.json');
$composerContent = is_file($composer) ? (string) file_get_contents($composer) : '';
$checks = [
    'ci_smoke' => is_file($pipeline),
    'qa_yaml' => is_file(applicating_path('.github/workflows/qa.yml')),
    'admin_smoke' => str_contains($pipelineContent, 'composer smoke:admin') && str_contains($localPipelineContent, 'composer smoke:admin') && str_contains($composerContent, 'smoke:admin'),
    'functional_readiness_smoke' => str_contains($pipelineContent, 'composer smoke:functional-readiness') && str_contains($localPipelineContent, 'composer smoke:functional-readiness') && str_contains($composerContent, 'smoke:functional-readiness'),
    'release_verify' => str_contains($composerContent, '"@smoke:admin"') && str_contains($composerContent, '"@smoke:functional-readiness"'),
];

$payload = [
    'tool' => 'applicating-pipeline-wiring',
    'status' => [] === array_filter($checks, static fn (bool $value): bool => !$value) ? 'complete' : 'incomplete',
    'checks' => $checks,
];

applicating_write_json('report/inspection/applicating-pipeline-wiring.json', $payload);
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === array_filter($checks, static fn (bool $value): bool => !$value) ? 0 : 1);
