<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$out = [];
$id = 1;
for ($i=0; $i<50; $i++) {
  $parent = $id;
  $out[] = ['id' => (string)$id, 'name' => 'root-'.$i, 'slug' => 'root-'.$i, 'parent' => '', 'locale' => 'en', 'published' => true];
  $id++;
  for ($j=0; $j<20; $j++) {
    $out[] = ['id' => (string)$id, 'name' => 'c-'.$i.'-'.$j, 'slug' => 'c-'.$i.'-'.$j, 'parent' => (string)$parent, 'locale' => 'en', 'published' => true];
    $id++;
  }
}
file_put_contents('report/category-testdata.json', json_encode($out));
file_put_contents('report/category-testdata-summary.json', json_encode([
  'count' => count($out),
  'depth' => 2,
  'locales' => ['en' => count($out)],
], JSON_PRETTY_PRINT));
echo "generated ".count($out)." categories\n";
