<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'graphql-config' => $root . '/config/graphql/catalog_category.yaml',
    'graphql-type' => $root . '/src/GraphQl/CategoryType.php',
    'graphql-resolver' => $root . '/src/GraphQl/CategoryResolver.php',
    'graphql-dump' => $root . '/tools/graphql/dump-category-schema.sh',
];
$missing = [];
foreach ($checks as $label => $path) {
    if (!file_exists($path)) {
        $missing[$label] = $path;
    }
}

if ($missing !== []) {
    foreach ($missing as $label => $path) {
        fwrite(STDERR, sprintf("[category-graphql-smoke] Missing %s: %s\n", $label, $path));
    }
    exit(1);
}

echo "[category-graphql-smoke] GraphQL entrypoints present.\n";
exit(0);
