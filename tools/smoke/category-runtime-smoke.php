<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checks = [
    'kernel' => $root . '/src/Kernel.php',
    'public-front-controller' => $root . '/public/index.php',
    'openapi' => $root . '/api/category-openapi.yaml',
    'dx-entrypoint' => $root . '/tools/dx/category-dx.php',
];

$missing = [];
foreach ($checks as $label => $path) {
    if (!is_file($path)) {
        $missing[$label] = $path;
    }
}

if ($missing !== []) {
    foreach ($missing as $label => $path) {
        fwrite(STDERR, sprintf("[category-runtime-smoke] Missing %s: %s\n", $label, $path));
    }
    exit(1);
}

echo "[category-runtime-smoke] Runtime entrypoints present.\n";
exit(0);
