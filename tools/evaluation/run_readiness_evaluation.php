<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$minScore = isset($_ENV['APP_READINESS_EVAL_MIN_SCORE']) ? (float) $_ENV['APP_READINESS_EVAL_MIN_SCORE'] : 1.0;
$profile = $_ENV['APP_READINESS_EVAL_PROFILE'] ?? 'strict';

$summary = [
    'total' => 1,
    'weightedScore' => applicating_has_vendor_autoload() ? 1.0 : 0.5,
];
$delta = [
    'weightedScoreDelta' => $summary['weightedScore'] - $minScore,
];
$policy = [
    'profile' => $profile,
    'shouldFail' => $summary['weightedScore'] < $minScore,
    'failedByRegression' => $summary['weightedScore'] < $minScore,
];

$report = [
    'summary' => $summary,
    'delta' => $delta,
    'policy' => $policy,
];

applicating_write_json('report/evaluation/application_readiness_evaluation.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
