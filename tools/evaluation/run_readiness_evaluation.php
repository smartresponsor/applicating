<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$scenarios = require $root . '/tools/evaluation/application_readiness_scenarios.php';
require_once $root . '/tools/evaluation/ApplicationReadinessEvaluator.php';

use App\Service\ApplicationReadinessService;

$kernel = require $root . '/config/bootstrap.php';
$container = $kernel->getContainer();
$service = $container->get(ApplicationReadinessService::class);

$evaluator = new \ApplicationReadinessEvaluator();

$results = [];
$total = 0;
$passed = 0;
$groupSummary = [];

foreach ($scenarios as $scenario) {
    $total++;

    $readiness = $service->buildReadiness($scenario['slug']);
    $evaluation = $evaluator->evaluate($scenario, $readiness);

    if ($evaluation['passed']) {
        $passed++;
    }

    $group = (string) ($scenario['group'] ?? 'default');
    if (!isset($groupSummary[$group])) {
        $groupSummary[$group] = ['total' => 0, 'passed' => 0];
    }
    $groupSummary[$group]['total']++;
    if ($evaluation['passed']) {
        $groupSummary[$group]['passed']++;
    }

    $results[] = [
        'scenario' => $scenario['name'],
        'group' => $group,
        'passed' => $evaluation['passed'],
        'mismatches' => $evaluation['mismatches'],
        'signals' => $readiness->signals,
    ];
}

$failed = $total - $passed;
$score = $total > 0 ? $passed / $total : 0.0;

$minScore = isset($_ENV['APP_READINESS_EVAL_MIN_SCORE']) ? (float) $_ENV['APP_READINESS_EVAL_MIN_SCORE'] : 1.0;
$minScore = max(0.0, min(1.0, $minScore));

$thresholdPassed = $score >= $minScore;

$outDir = $root . '/report/evaluation';
@mkdir($outDir, 0777, true);

file_put_contents(
    $outDir . '/application_readiness_evaluation.json',
    json_encode([
        'summary' => [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'score' => $score,
            'minScore' => $minScore,
            'thresholdPassed' => $thresholdPassed,
        ],
        'groups' => $groupSummary,
        'results' => $results,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

exit(0);
