<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$scenarios = require $root . '/tools/evaluation/application_readiness_scenarios.php';
require_once $root . '/tools/evaluation/ApplicationReadinessEvaluator.php';
require_once $root . '/tools/evaluation/ApplicationReadinessPolicyEvaluator.php';

use App\Service\ApplicationReadinessService;

$kernel = require $root . '/config/bootstrap.php';
$container = $kernel->getContainer();
$service = $container->get(ApplicationReadinessService::class);

$evaluator = new \ApplicationReadinessEvaluator();
$policyEvaluator = new \ApplicationReadinessPolicyEvaluator();

$results = [];
$total = 0;
$passed = 0;
$weightedTotal = 0;
$weightedPassed = 0;

foreach ($scenarios as $scenario) {
    $total++;
    $weight = (int) ($scenario['weight'] ?? 1);
    $weightedTotal += $weight;

    $readiness = $service->buildReadiness($scenario['slug']);
    $evaluation = $evaluator->evaluate($scenario, $readiness);

    if ($evaluation['passed']) {
        $passed++;
        $weightedPassed += $weight;
    }

    $results[] = [
        'scenario' => $scenario['name'],
        'group' => $scenario['group'] ?? 'default',
        'weight' => $weight,
        'passed' => $evaluation['passed'],
        'mismatches' => $evaluation['mismatches'],
    ];
}

$score = $total > 0 ? $passed / $total : 0.0;

$minScore = isset($_ENV['APP_READINESS_EVAL_MIN_SCORE']) ? (float) $_ENV['APP_READINESS_EVAL_MIN_SCORE'] : 1.0;
$minScore = max(0.0, min(1.0, $minScore));
$currentProfile = isset($_ENV['APP_READINESS_EVAL_PROFILE']) ? (string) $_ENV['APP_READINESS_EVAL_PROFILE'] : 'strict';
if (!in_array($currentProfile, ['strict', 'soft', 'dev'], true)) {
    $currentProfile = 'strict';
}
$thresholdPassed = $score >= $minScore;

$currentFailures = [];
foreach ($results as $result) {
    if (($result['passed'] ?? false) !== true) {
        $currentFailures[] = (string) $result['scenario'];
    }
}
sort($currentFailures);

$outDir = $root . '/report/evaluation';
$historyDir = $outDir . '/history';
@mkdir($outDir, 0777, true);
@mkdir($historyDir, 0777, true);

$latestPath = $historyDir . '/application_readiness_evaluation_latest.json';
$previous = is_file($latestPath) ? json_decode(file_get_contents($latestPath), true) : null;
$prevScore = $previous['summary']['weightedScore'] ?? $weightedScore;

$deltaScore = $weightedScore - $prevScore;

$anomaly = abs($deltaScore) > 0.2;

$trend = match (true) {
    $deltaScore > 0.05 => 'improving',
    $deltaScore < -0.05 => 'degrading',
    default => 'stable'
};

$summary = [
    'score' => $score,
    'weightedScore' => $weightedScore,
    'deltaWeightedScore' => $deltaScore,
    'trend' => $trend,
    'anomaly' => $anomaly,
];

$summary = [
    'total' => $total,
    'passed' => $passed,
    'failed' => $failed,
    'score' => $score,
    'minScore' => $minScore,
    'thresholdPassed' => $thresholdPassed,
];
$policy = $policyEvaluator->evaluate($currentProfile, $summary, $delta);

$report = [
    'summary' => $summary,
    'groups' => $groupSummary,
    'delta' => $delta,
    'policy' => $policy,
    'results' => $results,
];

$encoded = json_encode($report, JSON_PRETTY_PRINT);
file_put_contents($outDir.'/application_readiness_evaluation.json', $encoded);
file_put_contents($latestPath, $encoded);
file_put_contents($historyDir.'/application_readiness_evaluation_'.gmdate('Ymd_His').'.json', $encoded);

exit(($policy['shouldFail'] ?? false) === true ? 1 : 0);
