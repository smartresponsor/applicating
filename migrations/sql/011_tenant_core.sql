-- 011_tenant_core.sql

-- Registry
CREATE TABLE IF NOT EXISTS tenants (
  id VARCHAR(64) PRIMARY KEY,
  name TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  status TEXT NOT NULL DEFAULT 'active',  -- active/suspended/deleted
  plan TEXT NOT NULL DEFAULT 'free'
);

-- === Mode A: Row-Level Security (single DB, shared table with tenant_id) ===
-- Adds tenant_id to product if not present, enables RLS
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name='product' AND column_name='tenant_id'
  ) THEN
    ALTER TABLE product ADD COLUMN tenant_id VARCHAR(64) NOT NULL DEFAULT 'public';
    CREATE INDEX IF NOT EXISTS idx_product_tenant ON product(tenant_id);
  END IF;
END $$;

ALTER TABLE product ENABLE ROW LEVEL SECURITY;

-- Policy: tenant sees only own rows
DROP POLICY IF EXISTS product_isolation ON product;
CREATE POLICY product_isolation ON product
USING (tenant_id = current_setting('app.tenant_id', true));

-- === Mode B: Schema-per-tenant helpers ===
-- Function to create schema & clone basic objects
CREATE OR REPLACE FUNCTION ensure_tenant_schema(p_tenant text) RETURNS void AS $$
DECLARE
  q text;
BEGIN
  IF p_tenant IS NULL OR length(p_tenant)=0 THEN
    RAISE EXCEPTION 'tenant is empty';
  END IF;
  q := format('CREATE SCHEMA IF NOT EXISTS %I', p_tenant);
  EXECUTE q;
  -- Example: create product table if absent (minimal)
  q := format($$
    CREATE TABLE IF NOT EXISTS %I.product (
      LIKE public.product INCLUDING ALL
    )$$, p_tenant);
  EXECUTE q;
END;
$$ LANGUAGE plpgsql;
