<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'doctrine-config' => $root . '/config/packages/catalog_doctrine.yaml',
    'schema-check' => $root . '/tools/migration/check-category-schema.php',
    'entity-category' => $root . '/src/Entity/Category.php',
    'entity-taxonomy' => $root . '/src/Entity/CategoryTaxonomy.php',
];
$missing = [];
foreach ($checks as $label => $path) {
    if (!file_exists($path)) {
        $missing[$label] = $path;
    }
}

if ($missing !== []) {
    foreach ($missing as $label => $path) {
        fwrite(STDERR, sprintf("[category-doctrine-mapping-smoke] Missing %s: %s\n", $label, $path));
    }
    exit(1);
}

echo "[category-doctrine-mapping-smoke] Doctrine mapping prerequisites present.\n";
exit(0);
