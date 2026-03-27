-- migrations/sql/004_catalog_map.sql
CREATE TABLE IF NOT EXISTS product_category_map (
  product_id UUID NOT NULL REFERENCES product(id) ON DELETE CASCADE,
  category_id UUID NOT NULL REFERENCES product_category(id) ON DELETE CASCADE,
  PRIMARY KEY (product_id, category_id)
);

-- Денормализация атрибутов в read-модель (jsonb)
ALTER TABLE IF EXISTS product_read
  ADD COLUMN IF NOT EXISTS product_categories TEXT[] NULL,
  ADD COLUMN IF NOT EXISTS product_attrs JSONB NULL;

-- Индексы для фасетов
CREATE INDEX IF NOT EXISTS idx_product_read_categories ON product_read USING GIN (product_categories);
CREATE INDEX IF NOT EXISTS idx_product_read_attrs ON product_read USING GIN (product_attrs);
CREATE INDEX IF NOT EXISTS idx_product_read_status ON product_read (product_status);
CREATE INDEX IF NOT EXISTS idx_product_read_price ON product_read (product_price_amount);
