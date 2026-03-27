<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$data = [
  ['id' => '1', 'name' => 'Root', 'slug' => 'root', 'parent' => '', 'locale' => 'en'],
  ['id' => '2', 'name' => 'Electronics', 'slug' => 'electronics', 'parent' => '1', 'locale' => 'en'],
];
file_put_contents('report/category-export-flat.json', json_encode($data, JSON_PRETTY_PRINT));
echo "exported ".count($data)." categories\n";
