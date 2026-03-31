<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-fixture-publish-guard-report.json';

$fixturesPath = $root . '/src/DataFixtures/ApplicationFixtures.php';
$content = is_file($fixturesPath) ? (file_get_contents($fixturesPath) ?: '') : '';

$items = [
    [
        'name' => 'fixture_sets_review_required_governance',
        'path' => 'src/DataFixtures/ApplicationFixtures.php',
        'status' => str_contains($content, "review_required") ? 'present' : 'missing',
    ],
    [
        'name' => 'fixture_publishes_first_four_applications',
        'path' => 'src/DataFixtures/ApplicationFixtures.php',
        'status' => str_contains($content, 'if ($index <= 4)') ? 'present' : 'missing',
    ],
    [
        'name' => 'fixture_limits_publication_to_approved_governance_window',
        'path' => 'src/DataFixtures/ApplicationFixtures.php',
        'status' => str_contains($content, "governanceState = \$index <= 4 ? 'approved' : 'review_required'") ? 'guarded' : 'drift',
    ],
];

file_put_contents(
    $out,
    json_encode([
        'generatedAt' => date(DATE_ATOM),
        'count' => count($items),
        'items' => $items,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo sprintf(
    "[ApplicatingFixturePublishGuardReport] %d fixture publish-guard rows written to %s\n",
    count($items),
    str_replace($root . DIRECTORY_SEPARATOR, '', $out)
);

exit(0);
