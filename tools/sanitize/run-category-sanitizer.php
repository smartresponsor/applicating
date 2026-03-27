<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$base = __DIR__ . '/../../report';
$out = ['ok' => true, 'files' => []];
foreach (glob($base.'/*.json') as $file) {
    $raw = file_get_contents($file);
    json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $out['ok'] = false;
        $out['files'][] = ['file' => basename($file), 'error' => json_last_error_msg()];
    } else {
        $out['files'][] = ['file' => basename($file), 'error' => null];
    }
}
file_put_contents($base.'/category-sanitize-report.json', json_encode($out, JSON_PRETTY_PRINT));
