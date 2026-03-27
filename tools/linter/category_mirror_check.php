<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI: php tools/linter/category_mirror_check.php <project-root>
 * Ensures <Layer> <-> <LayerInterface> mirrors exist for Category responsibility paths.
 */
$root = $argv[1] ?? getcwd();
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "Invalid project root: {$root}\n");
    exit(2);
}

$layers = ['Entity', 'Service', 'Repository', 'Policy', 'Event', 'ValueObject'];
$fail = 0;

foreach ($layers as $layer) {
    $layerDir = $root . "/src/{$layer}/Category";
    if (!is_dir($layerDir)) {
        continue;
    }

    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($layerDir));
    foreach ($iter as $file) {
        if (!$file->isFile() || str_ends_with($file->getFilename(), '.php') === false) {
            continue;
        }

        $name = basename($file->getPathname(), '.php');
        $iface = $root . "/src/{$layer}Interface/Category/{$name}Interface.php";

        if ($layer !== 'Event' && !file_exists($iface)) {
            fwrite(STDERR, "Mirror missing for {$layer} {$name}: {$iface}\n");
            $fail++;
        }
    }
}

exit($fail > 0 ? 1 : 0);
