<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
$items = [
  ['loc' => 'https://example.com/category/root'],
  ['loc' => 'https://example.com/category/electronics'],
];
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($items as $it) {
  $xml .= "  <url><loc>{$it['loc']}</loc></url>\n";
}
$xml .= "</urlset>\n";
file_put_contents('public/category-sitemap.xml', $xml);
file_put_contents('report/category-sitemap-gen.json', json_encode(['count' => count($items)], JSON_PRETTY_PRINT));
echo "ok\n";
