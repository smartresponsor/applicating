<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-runtime-proof-report.json';

$checks = [
    'kernel' => $root . '/src/Kernel.php',
    'frontController' => $root . '/public/index.php',
    'referenceConfig' => $root . '/config/applicating_reference.php',
    'applicationEntity' => $root . '/src/Entity/Application.php',
    'applicationAdminController' => $root . '/src/Controller/ApplicationAdminController.php',
    'applicationApiController' => $root . '/src/Controller/ApplicationApiController.php',
    'applicationLifecycleService' => $root . '/src/Service/ApplicationLifecycleService.php',
    'applicationFixtures' => $root . '/src/DataFixtures/ApplicationFixtures.php',
    'applicationShowTemplate' => $root . '/templates/application/show.html.twig',
    'applicationPublishCommand' => $root . '/src/Command/ApplicatingApplicationPublishCommand.php',
];

$items = [];
foreach ($checks as $label => $path) {
    $items[] = [
        'check' => $label,
        'path' => str_replace($root . DIRECTORY_SEPARATOR, '', $path),
        'exists' => file_exists($path),
    ];
}

file_put_contents(
    $out,
    json_encode([
        'generatedAt' => date(DATE_ATOM),
        'items' => $items,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo sprintf(
    "[ApplicatingRuntimeProofReport] %d runtime proof rows written to %s\n",
    count($items),
    str_replace($root . DIRECTORY_SEPARATOR, '', $out)
);

exit(0);
