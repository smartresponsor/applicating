-- SmartCloud Edition
CREATE TABLE IF NOT EXISTS smartcloud_tenants (
  tenant_id TEXT PRIMARY KEY,
  plan TEXT NOT NULL,
  region TEXT NOT NULL,
  status TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS smartcloud_limits (
  tenant_id TEXT PRIMARY KEY,
  monthly_limit_usd NUMERIC NOT NULL DEFAULT 0,
  qps_limit INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tenant_usage (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  feature TEXT NOT NULL,
  quantity INT NOT NULL,
  unit_price_usd NUMERIC NOT NULL,
  amount_usd NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_tenant_usage_tenant_ts ON tenant_usage(tenant_id, ts DESC);

CREATE TABLE IF NOT EXISTS tenant_invoices (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  period TEXT NOT NULL, -- YYYY-MM or YYYY-MM-DD
  amount_usd NUMERIC NOT NULL,
  status TEXT NOT NULL, -- pending|paid|failed
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  paid_at TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS tenant_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  type TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  status TEXT NOT NULL
);
