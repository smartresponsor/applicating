<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$actual = ['category' => ['id','name','slug','locale','published']];
$expected = ['category' => ['id','name','slug','locale','published','channel']];
$diff = [];
foreach ($expected['category'] as $col) {
  if (!in_array($col, $actual['category'], true)) {
    $diff[] = ['table' => 'category', 'missing' => $col];
  }
}
file_put_contents('report/category-schema-diff.json', json_encode($diff, JSON_PRETTY_PRINT));
if ($diff) { exit(2); }
echo "schema-ok\n";
