<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$base = __DIR__ . '/../../';
$findings = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base)) as $file) {
  if (!$file->isFile()) continue;
  $path = $file->getPathname();
  if (preg_match('/secret|token|passwd/i', $path)) {
    $findings[] = ['file' => str_replace($base, '', $path), 'severity' => 'high'];
  }
}
file_put_contents($base.'report/category-security-findings.json', json_encode($findings, JSON_PRETTY_PRINT));
