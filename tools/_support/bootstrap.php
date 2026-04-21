<?php

declare(strict_types=1);

function applicating_root(): string
{
    return dirname(__DIR__, 2);
}

function applicating_path(string $relativePath): string
{
    return applicating_root() . '/' . ltrim($relativePath, '/');
}

function applicating_extract_quoted_value(string $value): string
{
    $value = ltrim($value);
    if ('' === $value) {
        return '';
    }

    $quote = $value[0];
    if ('"' !== $quote && '\'' !== $quote) {
        return '';
    }

    $end = strpos($value, $quote, 1);
    if (false === $end) {
        return '';
    }

    return substr($value, 1, $end - 1);
}

function applicating_ensure_directory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!@mkdir($path, 0777, true) && !is_dir($path)) {
        throw new RuntimeException(sprintf('Unable to create directory: %s', $path));
    }
}

function applicating_write_json(string $relativePath, array $payload): string
{
    $path = applicating_path($relativePath);
    applicating_ensure_directory(dirname($path));
    file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

    return $path;
}

function applicating_read_lines(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    return file($path, FILE_IGNORE_NEW_LINES) ?: [];
}

function applicating_scan_php_files(array $directories): array
{
    $files = [];

    foreach ($directories as $directory) {
        $path = applicating_path($directory);
        if (!is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || 'php' !== strtolower($fileInfo->getExtension())) {
                continue;
            }

            $files[] = $fileInfo->getPathname();
        }
    }

    sort($files);

    return $files;
}

function applicating_parse_routes_from_controller(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if (false === $content) {
        return [];
    }

    $lines = preg_split('/\R/', $content) ?: [];
    $routes = [];
    $classPrefix = '';
    $lineCount = count($lines);

    for ($index = 0; $index < $lineCount; ++$index) {
        $line = trim($lines[$index]);
        if (!str_starts_with($line, '#[Route(')) {
            continue;
        }

        $payload = preg_replace('/^#\[Route\((.*)\)\]$/', '$1', $line) ?? '';
        $nextLine = '';
        for ($lookahead = $index + 1; $lookahead < $lineCount; ++$lookahead) {
            $candidate = trim($lines[$lookahead]);
            if ('' === $candidate) {
                continue;
            }

            $nextLine = $candidate;
            break;
        }

        if (str_contains($nextLine, 'class ')) {
            $classPrefix = applicating_extract_quoted_value($payload);
            continue;
        }

        if (!str_contains($nextLine, 'function ')) {
            continue;
        }

        $routePath = applicating_extract_quoted_value($payload);
        $name = null;
        $namePos = strpos($payload, 'name:');
        if (false !== $namePos) {
            $name = applicating_extract_quoted_value(substr($payload, $namePos + 5));
            if ('' === $name) {
                $name = null;
            }
        }

        preg_match('/methods:\s*\[(.*?)\]/s', $payload, $methodsMatch);
        $methods = [];
        if (isset($methodsMatch[1])) {
            foreach (explode(',', $methodsMatch[1]) as $method) {
                $clean = trim($method);
                $clean = str_replace(['"', "'"], '', $clean);
                if ('' !== $clean) {
                    $methods[] = $clean;
                }
            }
        }

        $fullPath = $classPrefix . $routePath;
        if ('' === $fullPath) {
            $fullPath = '/';
        }

        $routes[] = [
            'path' => $fullPath,
            'name' => $name,
            'methods' => $methods,
        ];
    }

    return $routes;
}

function applicating_detect_namespace_issues(array $phpFiles): array
{
    $issues = [];

    foreach ($phpFiles as $path) {
        $relative = str_replace(applicating_root() . '/', '', $path);
        if (str_starts_with($relative, 'src/')) {
            $content = file_get_contents($path);
            if (false === $content) {
                continue;
            }

            if (!preg_match('/^namespace\s+App\\Application\\/m', $content)) {
                $issues[] = $relative;
            }
        }
    }

    return $issues;
}

function applicating_collect_directory_listing(string $directory): array
{
    $path = applicating_path($directory);
    if (!is_dir($path)) {
        return [];
    }

    $entries = [];
    foreach (scandir($path) ?: [] as $entry) {
        if ('.' === $entry || '..' === $entry) {
            continue;
        }
        $entries[] = $entry;
    }

    sort($entries);

    return $entries;
}

function applicating_has_vendor_autoload(): bool
{
    return is_file(applicating_path('vendor/autoload.php'));
}
