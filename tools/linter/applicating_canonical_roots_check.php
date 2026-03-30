<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI: php tools/linter/applicating_canonical_roots_check.php <project-root>
 * Enforces canonical Symfony-oriented directory rules for the active Applicating component.
 */
$root = $argv[1] ?? getcwd();
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "Invalid project root: {$root}\n");
    exit(2);
}

$fail = 0;

$forbiddenSrcRoots = [
    'src/Applicating',
    'src/ApplicatingInterface',
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

        if (preg_match('~^src/.+/(Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden nested namespace path: {$relative}\n");
            $fail++;
            continue;
        }

        if (preg_match('~^tests/(Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden tests root path: {$relative}\n");
            $fail++;
            continue;
        }

        if (preg_match('~^tests/.+/(Domain)(/|$)~', $relative) === 1) {
            fwrite(STDERR, "Forbidden nested tests path: {$relative}\n");
            $fail++;
        }
    }
}

exit($fail > 0 ? 1 : 0);
