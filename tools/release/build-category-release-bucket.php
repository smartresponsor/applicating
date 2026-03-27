<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$packs = ['category-rc1-m-pack.zip', 'category-rc1-n-pack.zip', 'category-rc1-o-pack.zip', 'category-rc1-p-pack.zip', 'category-rc1-q-pack.zip', 'category-rc1-r-pack.zip', 'category-rc1-s-pack.zip', 'category-rc1-t-pack.zip', 'category-rc1-u-pack.zip', 'category-rc1-v-pack.zip', 'category-rc1-w-pack.zip', 'category-rc1-x-pack.zip', 'category-rc1-y-pack.zip'];
$base = '/mnt/data';
$out = [];
foreach ($packs as $p) {
    $exists = file_exists($base.'/'.$p);
    $out[] = ['name' => $p, 'exists' => $exists];
}
file_put_contents(__DIR__.'/../../report/category-rc1-release-bucket.json', json_encode($out, JSON_PRETTY_PRINT));
