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
$previousReport = null;
if (is_file($latestPath)) {
    $decoded = json_decode((string) file_get_contents($latestPath), true);
    if (is_array($decoded)) {
        $previousReport = $decoded;
    }
}

$previousSummary = is_array($previousReport['summary'] ?? null) ? $previousReport['summary'] : [];
$previousResults = is_array($previousReport['results'] ?? null) ? $previousReport['results'] : [];
$previousFailures = [];
foreach ($previousResults as $result) {
    if (($result['passed'] ?? false) !== true && isset($result['scenario'])) {
        $previousFailures[] = (string) $result['scenario'];
    }
}
sort($previousFailures);

$newFailures = array_values(array_diff($currentFailures, $previousFailures));
$resolvedFailures = array_values(array_diff($previousFailures, $currentFailures));

$delta = [
    'hasPreviousRun' => null !== $previousReport,
    'scoreChange' => $score - (float) ($previousSummary['score'] ?? 0.0),
    'passedChange' => $passed - (int) ($previousSummary['passed'] ?? 0),
    'failedChange' => $failed - (int) ($previousSummary['failed'] ?? 0),
    'newFailures' => $newFailures,
    'resolvedFailures' => $resolvedFailures,
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

$timestamp = gmdate('Ymd_His');
$currentPath = $outDir . '/application_readiness_evaluation.json';
$historyPath = $historyDir . '/application_readiness_evaluation_' . $timestamp . '.json';

$encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (false === $encoded) {
    fwrite(STDERR, "Unable to encode evaluation report.\n");
    exit(1);
}

file_put_contents($currentPath, $encoded);
file_put_contents($latestPath, $encoded);
file_put_contents($historyPath, $encoded);

exit(($policy['shouldFail'] ?? false) === true ? 1 : 0);
