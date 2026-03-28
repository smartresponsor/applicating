<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src'));
$failures = [];

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || $file->isDir() || 'php' !== $file->getExtension()) {
        continue;
    }

    $command = sprintf('php -l %s', escapeshellarg($file->getPathname()));
    exec($command, $output, $exitCode);
    if (0 !== $exitCode) {
        $failures[] = implode(PHP_EOL, $output);
    }
}

if ([] !== $failures) {
    fwrite(STDERR, implode(PHP_EOL.PHP_EOL, $failures).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Application PHP lint passed.\n");
