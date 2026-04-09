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

    preg_match_all('/#\[Route\((.*?)\)\]/s', $content, $matches, PREG_SET_ORDER);
    $routes = [];

    foreach ($matches as $match) {
        $payload = $match[1] ?? '';
        preg_match('/[\'\"]([^\'\"]+)[\'\"]/', $payload, $pathMatch);
        preg_match('/name:\s*[\'\"]([^\'\"]+)[\'\"]/', $payload, $nameMatch);
        preg_match('/methods:\s*\[(.*?)\]/s', $payload, $methodsMatch);

        $methods = [];
        if (isset($methodsMatch[1])) {
            foreach (explode(',', $methodsMatch[1]) as $method) {
                $clean = trim($method);
                $clean = trim($clean, "'\"");
                if ('' !== $clean) {
                    $methods[] = $clean;
                }
            }
        }

        $routes[] = [
            'path' => $pathMatch[1] ?? null,
            'name' => $nameMatch[1] ?? null,
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

            if (!preg_match('/^namespace\s+App\\\\/m', $content)) {
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
