<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

/**
 * CLI: php tools/Category/rescue/tools_linter_category_prefix_check.php <project-root>
 * Ensures classes under Category responsibility paths start with 'Category'.
 */

$root = $argv[1] ?? getcwd();
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$fail = 0;

foreach ($rii as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    if (!preg_match('~(?:^|/)src/.*/Category/[^/]+\.php$~', $path)) {
        continue;
    }

    $name = basename($path, '.php');
    if (!preg_match('/^Category([A-Z].*)?$|^Category$/', $name)) {
        fwrite(STDERR, "Prefix violation: {$file->getPathname()}\n");
        ++$fail;
    }
}

exit($fail > 0 ? 1 : 0);
