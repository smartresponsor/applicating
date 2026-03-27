<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * CLI: php tools/linter/app_namespace_check.php <project-root>
 * Enforces canonical Symfony namespace root: App\\ => src/
 * and forbids Smartresponsor/SmartResponsor namespace usage.
 */
$root = $argv[1] ?? getcwd();
if (!is_string($root) || !is_dir($root)) {
    fwrite(STDERR, "Invalid project root: {$root}\n");
    exit(2);
}

$fail = 0;
$composerPath = rtrim($root, '/\\').'/composer.json';
if (!is_file($composerPath)) {
    fwrite(STDERR, "Missing composer.json: {$composerPath}\n");
    exit(1);
}

$composerJson = file_get_contents($composerPath);
if (!is_string($composerJson)) {
    fwrite(STDERR, "Unable to read composer.json: {$composerPath}\n");
    exit(1);
}

$composer = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);
$autoload = $composer['autoload']['psr-4'] ?? [];
$autoloadDev = $composer['autoload-dev']['psr-4'] ?? [];

if (!is_array($autoload) || count($autoload) !== 1 || ($autoload['App\\'] ?? null) !== 'src/') {
    fwrite(STDERR, "Invalid autoload psr-4 root. Expected only App\\\\ => src/\n");
    $fail++;
}
if (!is_array($autoloadDev) || count($autoloadDev) !== 1 || ($autoloadDev['App\\Tests\\'] ?? null) !== 'tests/') {
    fwrite(STDERR, "Invalid autoload-dev psr-4 root. Expected only App\\\\Tests\\\\ => tests/\n");
    $fail++;
}
if (preg_match('/"Smartresponsor\\\\\\\\|"SmartResponsor\\\\\\\\/u', $composerJson) === 1) {
    fwrite(STDERR, "Forbidden namespace root found in composer.json\n");
    $fail++;
}

$scanRoots = ['src', 'tests', 'tools'];
foreach ($scanRoots as $dir) {
    $path = rtrim($root, '/\\').'/'.$dir;
    if (!is_dir($path)) {
        continue;
    }

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($rii as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $fullPath = str_replace('\\', '/', $file->getPathname());
        if (preg_match('/\.php$/', $fullPath) !== 1) {
            continue;
        }

        $content = file_get_contents($fullPath);
        if (!is_string($content)) {
            fwrite(STDERR, "Unable to read file: {$fullPath}\n");
            $fail++;
            continue;
        }

        if (preg_match('/\b(?:namespace|use)\s+Smart(?:responsor|Responsor)\\\\/u', $content) === 1) {
            fwrite(STDERR, "Forbidden namespace usage: {$fullPath}\n");
            $fail++;
        }
    }
}

exit($fail > 0 ? 1 : 0);
