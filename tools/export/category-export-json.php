<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$data = [
  ['id' => 1, 'slug' => 'root', 'name' => 'Root'],
  ['id' => 2, 'slug' => 'electronics', 'name' => 'Electronics'],
];
file_put_contents('report/category-export.json', json_encode($data, JSON_PRETTY_PRINT));
echo "exported " . count($data) . " categories\n";
