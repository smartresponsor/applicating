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

foreach ($scenarios as $scenario) {
    $readiness = $service->buildReadiness($scenario['slug']);
    $evaluation = $evaluator->evaluate($scenario, $readiness);

    $results[] = [
        'scenario' => $scenario['name'],
        'group' => $scenario['group'] ?? 'default',
        'passed' => $evaluation['passed'],
        'mismatches' => $evaluation['mismatches'],
        'signals' => $readiness->signals,
    ];
}

$outDir = $root . '/report/evaluation';
@mkdir($outDir, 0777, true);

file_put_contents(
    $outDir . '/application_readiness_evaluation.json',
    json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

exit(0);
