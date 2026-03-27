<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$file = __DIR__ . '/../../report/category-slo-breach.json';
$list = is_file($file) ? json_decode(file_get_contents($file), true) : [];
$list[] = [
  'id' => bin2hex(random_bytes(4)),
  'ts' => date(DATE_ATOM),
  'reason' => 'slo-breach',
];
file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT));
