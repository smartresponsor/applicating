-- Demo seed (fallback SQL)
INSERT INTO product (product_sku, product_title_v2, product_slug, product_brand, product_price_amount, product_price_currency, product_status, product_stock)
VALUES 
('SKU1','Demo Product #1','demo-product-1','Acme',15000,'USD','active',100),
('SKU2','Demo Product #2','demo-product-2','Globex',9900,'USD','active',80),
('SKU3','Demo Product #3','demo-product-3','Umbrella',12900,'USD','active',75)
ON CONFLICT (product_sku) DO NOTHING;
