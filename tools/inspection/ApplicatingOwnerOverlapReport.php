<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$reportDir = $root . '/report/inspection';
@mkdir($reportDir, 0777, true);
$out = $reportDir . '/applicating-owner-overlap-report.json';

$targets = [
    $root . '/composer.json',
    $root . '/config',
    $root . '/docs',
    $root . '/src',
    $root . '/templates',
    $root . '/tests',
    $root . '/tools',
];

$markers = ['Catalog', 'Category'];
$items = [];

foreach ($targets as $target) {
    if (is_file($target)) {
        $content = file_get_contents($target) ?: '';
        $hits = [];
        foreach ($markers as $marker) {
            if (str_contains($content, $marker)) {
                $hits[] = $marker;
            }
        }

        if ([] !== $hits) {
            $items[] = [
                'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $target),
                'markers' => $hits,
            ];
        }

        continue;
    }

    if (!is_dir($target)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $content = file_get_contents($file->getPathname()) ?: '';
        $hits = [];
        foreach ($markers as $marker) {
            if (str_contains($content, $marker)) {
                $hits[] = $marker;
            }
        }

        if ([] === $hits) {
            continue;
        }

        $items[] = [
            'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname()),
            'markers' => $hits,
        ];
    }
}

file_put_contents(
    $out,
    json_encode([
        'generatedAt' => date(DATE_ATOM),
        'count' => count($items),
        'items' => $items,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

echo sprintf("[ApplicatingOwnerOverlapReport] %d rows written to %s\n", count($items), str_replace($root . DIRECTORY_SEPARATOR, '', $out));

exit(0);
