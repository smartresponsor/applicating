<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2).'/config';
$failures = [];
$allowedFiles = ['bootstrap.php', 'bundles.php'];
$generatedLegacyReference = $root.'/reference.php';

if (is_file($generatedLegacyReference)) {
    unlink($generatedLegacyReference);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->isDir()) {
        continue;
    }

    $filename = $file->getFilename();
    if (in_array($filename, $allowedFiles, true)) {
        continue;
    }

    if (preg_match('/\.(yaml|yml|json|php)$/', $filename) !== 1) {
        continue;
    }

    if (!str_starts_with($filename, 'applicating_') && !str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'policies'.DIRECTORY_SEPARATOR)) {
        $failures[] = $file->getPathname();
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Config files without applicating_ prefix:\n".implode("\n", $failures)."\n");
    exit(1);
}

fwrite(STDOUT, "Applicating config prefix check passed.\n");
