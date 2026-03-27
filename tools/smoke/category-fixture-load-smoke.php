<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'fixture-loader' => $root . '/tools/demo/load-category-fixtures.sh',
    'fixture-seed' => $root . '/tools/demo/seed-category.sh',
    'fixtures-class' => $root . '/src/DataFixtures/CategoryFixtures.php',
];
$missing = [];
foreach ($checks as $label => $path) {
    if (!is_file($path)) {
        $missing[$label] = $path;
    }
}

if ($missing !== []) {
    foreach ($missing as $label => $path) {
        fwrite(STDERR, sprintf("[category-fixture-load-smoke] Missing %s: %s\n", $label, $path));
    }
    exit(1);
}

echo "[category-fixture-load-smoke] Fixture load entrypoints present.\n";
exit(0);
