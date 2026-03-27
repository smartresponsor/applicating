<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI: php tools/linter/catalog_config_prefix_check.php <project-root>
 * Ensures component YAML config filenames under /config start with "catalog_".
 */
$root = $argv[1] ?? getcwd();
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "Invalid project root: {$root}\n");
    exit(2);
}

$configRoot = rtrim($root, '/\\').'/config';
if (!is_dir($configRoot)) {
    fwrite(STDERR, "Config directory not found: {$configRoot}\n");
    exit(1);
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configRoot));
$fail = 0;

foreach ($rii as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    if (preg_match('~\.ya?ml$~', $path) !== 1) {
        continue;
    }

    $name = basename($path);
    if (str_starts_with($name, 'catalog_')) {
        continue;
    }

    fwrite(STDERR, "Config prefix violation: {$path}\n");
    $fail++;
}

exit($fail > 0 ? 1 : 0);
