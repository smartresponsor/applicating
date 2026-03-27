-- Billing & Usage Engine
CREATE TABLE IF NOT EXISTS billing_wallets (
  tenant_id TEXT PRIMARY KEY,
  balance_usd NUMERIC NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS billing_prices (
  id BIGSERIAL PRIMARY KEY,
  feature TEXT NOT NULL UNIQUE,
  unit_price_usd NUMERIC NOT NULL
);

CREATE TABLE IF NOT EXISTS billing_usage (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  feature TEXT NOT NULL,
  quantity INT NOT NULL,
  unit_price_usd NUMERIC NOT NULL,
  amount_usd NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_usage_tenant_ts ON billing_usage(tenant_id, ts DESC);

CREATE TABLE IF NOT EXISTS billing_invoices (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  period TEXT NOT NULL, -- YYYY-MM
  amount_usd NUMERIC NOT NULL,
  status TEXT NOT NULL, -- pending|paid|failed
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  paid_at TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS billing_payments (
  id BIGSERIAL PRIMARY KEY,
  invoice_id INT NOT NULL,
  method TEXT NOT NULL, -- stripe|paypal|crypto
  amount_usd NUMERIC NOT NULL,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS billing_limits (
  tenant_id TEXT PRIMARY KEY,
  monthly_limit_usd NUMERIC NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS billing_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  type TEXT NOT NULL, -- topup|charge|limit
  meta JSONB NOT NULL DEFAULT '{}'::jsonb
);
