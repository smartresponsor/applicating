<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$targets = ['src', 'tests', 'config', 'public', 'tools'];
$files = [];

foreach ($targets as $target) {
    $path = $root . DIRECTORY_SEPARATOR . $target;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $files[] = $file->getPathname();
    }
}

sort($files);

if ($files === []) {
    fwrite(STDERR, "[CatalogPhpLint] No PHP files found.\n");
    exit(1);
}

$errors = [];
foreach ($files as $file) {
    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1';
    exec($cmd, $output, $exitCode);
    if ($exitCode !== 0) {
        $errors[] = ['file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file), 'output' => implode(PHP_EOL, $output)];
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, sprintf("[CatalogPhpLint] %s\n%s\n", $error['file'], $error['output']));
    }
    exit(1);
}

echo sprintf("[CatalogPhpLint] OK: %d PHP files linted.\n", count($files));
exit(0);
