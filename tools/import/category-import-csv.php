<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$in = $argv[1] ?? 'category.csv';
if (!is_file($in)) {
    fwrite(STDERR, "file not found\n");
    exit(1);
}
$rows = array_map('str_getcsv', file($in));
$out = [];
foreach ($rows as $r) {
    $out[] = ['id' => $r[0] ?? null, 'slug' => $r[1] ?? null, 'name' => $r[2] ?? null];
}
file_put_contents('report/category-import-from-csv.json', json_encode($out, JSON_PRETTY_PRINT));
echo "imported " . count($out) . " categories\n";
