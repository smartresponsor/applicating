-- ABI audit storage
CREATE TABLE IF NOT EXISTS abi_audit_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  revenue_forecast NUMERIC NOT NULL,
  action TEXT NOT NULL,
  outcome TEXT NOT NULL,
  details JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE TABLE IF NOT EXISTS pricing (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  base_price_cents INT NOT NULL DEFAULT 1000
);
CREATE TABLE IF NOT EXISTS sla_plans (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  target_uptime TEXT NOT NULL DEFAULT '99.5'
);
CREATE TABLE IF NOT EXISTS credits (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  credit_cents INT NOT NULL,
  reason TEXT
);
