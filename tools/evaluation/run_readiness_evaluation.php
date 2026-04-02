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
$weightedScore = $weightedTotal > 0 ? $weightedPassed / $weightedTotal : $score;

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

$policy = $policyEvaluator->evaluate('strict', $summary, ['newFailures'=>[]]);

$report = [
    'summary' => $summary,
    'policy' => $policy,
    'results' => $results,
];

$encoded = json_encode($report, JSON_PRETTY_PRINT);
file_put_contents($outDir.'/application_readiness_evaluation.json', $encoded);
file_put_contents($latestPath, $encoded);
file_put_contents($historyDir.'/application_readiness_evaluation_'.gmdate('Ymd_His').'.json', $encoded);

exit(0);
