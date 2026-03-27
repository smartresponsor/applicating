<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$rows = [
  ['id' => 1, 'name' => 'Root', 'slug' => 'root', 'parent' => '', 'locale' => 'en', 'channel' => 'default'],
  ['id' => 2, 'name' => 'Electronics', 'slug' => 'electronics', 'parent' => '1', 'locale' => 'en', 'channel' => 'default'],
];
$fname = 'report/category-export-'.date('YmdHis').'.csv';
$f = fopen($fname, 'w');
fputcsv($f, ['id','name','slug','parent','locale','channel']);
foreach ($rows as $r) {
  fputcsv($f, [$r['id'],$r['name'],$r['slug'],$r['parent'],$r['locale'],$r['channel']]);
}
fclose($f);
$indexFile = 'report/category-export-index.json';
$index = [];
if (is_file($indexFile)) {
  $index = json_decode(file_get_contents($indexFile), true) or [];
}
$index[] = ['file' => $fname, 'rows' => count($rows)];
file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT));
echo $fname."\n";
