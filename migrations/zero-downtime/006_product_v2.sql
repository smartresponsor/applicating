-- 006_product_v2.sql
-- Goal: migrate Product schema to v2 without downtime.
-- Strategy: additive changes + shadow columns + dual writes via triggers + backfill + cutover + cleanup.
-- Notes:
-- - Assumes existing tables from earlier iterations: product, product_read, product_outbox, product_attribute, product_category_map, etc.
-- - All new columns use snake_case.

BEGIN;

-- 1) Additive changes (shadow columns)
ALTER TABLE IF EXISTS product
  ADD COLUMN IF NOT EXISTS product_title_v2 TEXT,
  ADD COLUMN IF NOT EXISTS product_slug TEXT,
  ADD COLUMN IF NOT EXISTS product_brand TEXT,
  ADD COLUMN IF NOT EXISTS product_meta JSONB DEFAULT '{}'::jsonb;

-- Ensure read model has matching projection fields
ALTER TABLE IF EXISTS product_read
  ADD COLUMN IF NOT EXISTS product_slug TEXT,
  ADD COLUMN IF NOT EXISTS product_brand TEXT,
  ADD COLUMN IF NOT EXISTS product_meta JSONB DEFAULT '{}'::jsonb;

-- 2) Function to keep product_read in sync (dual-write)
CREATE OR REPLACE FUNCTION sync_product_read_v2() RETURNS TRIGGER AS $$
BEGIN
  IF (TG_OP = 'INSERT') THEN
    INSERT INTO product_read(id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock, updated_at, product_slug, product_brand, product_meta)
    VALUES (NEW.id, NEW.product_sku, COALESCE(NEW.product_title_v2, NEW.product_title), NEW.product_price_amount, NEW.product_price_currency, NEW.product_status, NEW.product_stock, NOW(), NEW.product_slug, NEW.product_brand, COALESCE(NEW.product_meta, '{}'::jsonb))
    ON CONFLICT (id) DO UPDATE SET
      product_sku = EXCLUDED.product_sku,
      product_title = EXCLUDED.product_title,
      product_price_amount = EXCLUDED.product_price_amount,
      product_price_currency = EXCLUDED.product_price_currency,
      product_status = EXCLUDED.product_status,
      product_stock = EXCLUDED.product_stock,
      updated_at = NOW(),
      product_slug = EXCLUDED.product_slug,
      product_brand = EXCLUDED.product_brand,
      product_meta = EXCLUDED.product_meta;
    RETURN NEW;
  ELSIF (TG_OP = 'UPDATE') THEN
    UPDATE product_read SET
      product_sku = NEW.product_sku,
      product_title = COALESCE(NEW.product_title_v2, NEW.product_title),
      product_price_amount = NEW.product_price_amount,
      product_price_currency = NEW.product_price_currency,
      product_status = NEW.product_status,
      product_stock = NEW.product_stock,
      updated_at = NOW(),
      product_slug = NEW.product_slug,
      product_brand = NEW.product_brand,
      product_meta = COALESCE(NEW.product_meta, '{}'::jsonb)
    WHERE id = NEW.id;
    RETURN NEW;
  ELSIF (TG_OP = 'DELETE') THEN
    DELETE FROM product_read WHERE id = OLD.id;
    RETURN OLD;
  END IF;
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- 3) Attach triggers for dual writes (AFTER INSERT/UPDATE/DELETE on write model)
DROP TRIGGER IF EXISTS trg_product_dualwrite_v2 ON product;
CREATE TRIGGER trg_product_dualwrite_v2
AFTER INSERT OR UPDATE OR DELETE ON product
FOR EACH ROW EXECUTE FUNCTION sync_product_read_v2();

-- 4) Backfill helper function (safe to run multiple times)
CREATE OR REPLACE FUNCTION backfill_product_read_v2(batch_size INT DEFAULT 1000) RETURNS INT AS $$
DECLARE
  cnt INT := 0;
BEGIN
  WITH to_sync AS (
    SELECT p.*
    FROM product p
    LEFT JOIN product_read r ON r.id = p.id
    WHERE r.id IS NULL OR r.updated_at < NOW() - INTERVAL '1 minute'
    ORDER BY p.updated_at NULLS FIRST, p.id
    LIMIT batch_size
  )
  INSERT INTO product_read(id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock, updated_at, product_slug, product_brand, product_meta)
  SELECT id, product_sku, COALESCE(product_title_v2, product_title), product_price_amount, product_price_currency, product_status, product_stock, NOW(), product_slug, product_brand, COALESCE(product_meta, '{}'::jsonb)
  FROM to_sync
  ON CONFLICT (id) DO UPDATE SET
    product_sku = EXCLUDED.product_sku,
    product_title = EXCLUDED.product_title,
    product_price_amount = EXCLUDED.product_price_amount,
    product_price_currency = EXCLUDED.product_price_currency,
    product_status = EXCLUDED.product_status,
    product_stock = EXCLUDED.product_stock,
    updated_at = NOW(),
    product_slug = EXCLUDED.product_slug,
    product_brand = EXCLUDED.product_brand,
    product_meta = EXCLUDED.product_meta;

  GET DIAGNOSTICS cnt = ROW_COUNT;
  RETURN cnt;
END;
$$ LANGUAGE plpgsql;

-- 5) Optional: view for v2 API (maps legacy + v2 fields)
CREATE OR REPLACE VIEW product_read_v2 AS
SELECT
  id,
  product_sku,
  product_title,
  product_slug,
  product_brand,
  product_price_amount,
  product_price_currency,
  product_status,
  product_stock,
  product_categories,
  product_attrs,
  product_meta,
  updated_at
FROM product_read;

COMMIT;
