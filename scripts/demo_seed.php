<?php
declare(strict_types=1);
$dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;port=5432;dbname=app;user=app;password=app';
$db = new PDO($dsn);
echo "[seed] inserting sample products...\n";
for ($i=1; $i<=10; $i++) {
  $title = "Demo Product #{$i}";
  $slug = "demo-product-{$i}";
  $brand = ["Acme","Globex","Umbrella","Soylent"][array_rand(["a","b","c","d"])];
  $price = rand(10,200) * 100;
  $db->exec("INSERT INTO product (product_sku, product_title_v2, product_slug, product_brand, product_price_amount, product_price_currency, product_status, product_stock) 
             VALUES ('SKU{$i}', '{$title}', '{$slug}', '{$brand}', {$price}, 'USD', 'active', 100)
             ON CONFLICT (product_sku) DO NOTHING;");
}
echo "[seed] done.\n";
