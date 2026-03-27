<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'fixture-class' => $root . '/src/DataFixtures/CategoryFixtures.php',
    'fixture-loader' => $root . '/tools/demo/load-category-fixtures.sh',
    'fixture-seed' => $root . '/tools/demo/seed-category.sh',
];
$missing = [];
foreach ($checks as $label => $path) {
    if (!is_file($path)) {
        $missing[$label] = $path;
    }
}

if (!is_dir($root . '/fixtures')) {
    $missing['fixtures-directory'] = $root . '/fixtures';
}

if ($missing !== []) {
    foreach ($missing as $label => $path) {
        fwrite(STDERR, sprintf("[category-fixture-sanity] Missing %s: %s\n", $label, $path));
    }
    exit(1);
}

echo "[category-fixture-sanity] Fixture entrypoints present.\n";
exit(0);
