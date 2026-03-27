-- Pricing rules & experiments
CREATE TABLE IF NOT EXISTS pricing_rules (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  sku TEXT NOT NULL,
  base_price_cents INT NOT NULL,
  elasticity NUMERIC NOT NULL DEFAULT -1.2,
  min_margin NUMERIC NOT NULL DEFAULT 0.25,
  floor_cents INT NOT NULL DEFAULT 500,
  ceil_cents INT NOT NULL DEFAULT 100000,
  UNIQUE(tenant_id, sku)
);

CREATE TABLE IF NOT EXISTS price_tests (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  sku TEXT NOT NULL,
  variant TEXT NOT NULL, -- A/B
  price_cents INT NOT NULL,
  users INT NOT NULL DEFAULT 0,
  orders INT NOT NULL DEFAULT 0,
  revenue_cents INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS price_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  sku TEXT NOT NULL,
  base_price_cents INT NOT NULL,
  computed_price_cents INT NOT NULL,
  guard TEXT NOT NULL,
  details JSONB NOT NULL DEFAULT '{}'::jsonb
);
