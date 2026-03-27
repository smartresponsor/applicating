<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI: php tools/linter/category_canonical_roots_check.php <project-root>
 * Enforces canonical directory rules for src/ and tests/.
 */
$root = $argv[1] ?? getcwd();
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "Invalid project root: {$root}\n");
    exit(2);
}

$fail = 0;

$forbiddenSrcRoots = [
    'src/Catalog',
    'src/CatalogInterface',
    'src/Cataloging',
    'src/CatalogingInterface',
    'src/Domain',
    'src/DomainInterface',
    'src/Port',
    'src/Adaptor',
    'src/Infra',
    'src/opr',
];

foreach ($forbiddenSrcRoots as $forbiddenRoot) {
    if (is_dir($root . '/' . $forbiddenRoot)) {
        fwrite(STDERR, "Forbidden root detected: {$forbiddenRoot}\n");
        $fail++;
    }
}

$scanRoots = ['src', 'tests'];

foreach ($scanRoots as $scanRoot) {
    $scanPath = $root . '/' . $scanRoot;
    if (!is_dir($scanPath)) {
        continue;
    }

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iter as $item) {
        if (!$item->isDir()) {
            continue;
        }

        $path = str_replace('\\', '/', $item->getPathname());
        $relative = ltrim(substr($path, strlen($root)), '/');

        if ($relative === 'src/Entity/Catalog' || str_starts_with($relative, 'src/Entity/Catalog/')) {
            continue;
        }

        if (preg_match('~^src/.+/(Catalog|Cataloging|Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden nested namespace path: {$relative}\n");
            $fail++;
            continue;
        }

        if (preg_match('~^tests/(Catalog|Cataloging|Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden tests root path: {$relative}\n");
            $fail++;
            continue;
        }

        if (preg_match('~^tests/.+/(Catalog|Cataloging|Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden nested tests path: {$relative}\n");
            $fail++;
        }
    }
}

exit($fail > 0 ? 1 : 0);
