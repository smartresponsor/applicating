-- Staging & fact tables for ETL
-- Staging tables
CREATE TABLE IF NOT EXISTS staging_usage (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  metric TEXT NOT NULL,
  value NUMERIC NOT NULL DEFAULT 0,
  processed BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS staging_billing (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  invoice_id TEXT NOT NULL,
  amount_cents INT NOT NULL,
  currency TEXT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  status TEXT NOT NULL,
  processed BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS staging_crm (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  customer_id TEXT NOT NULL,
  event TEXT NOT NULL,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  meta JSONB NOT NULL DEFAULT '{}'::jsonb,
  processed BOOLEAN DEFAULT FALSE
);

-- Facts / Dims
CREATE TABLE IF NOT EXISTS fact_usage_daily (
  d DATE NOT NULL,
  tenant_id TEXT NOT NULL,
  metric TEXT NOT NULL,
  value NUMERIC NOT NULL,
  PRIMARY KEY (d, tenant_id, metric)
);

CREATE TABLE IF NOT EXISTS fact_billing (
  tenant_id TEXT NOT NULL,
  period TEXT NOT NULL, -- YYYY-MM
  amount_cents INT NOT NULL,
  currency TEXT NOT NULL,
  invoices INT NOT NULL DEFAULT 0,
  PRIMARY KEY (tenant_id, period)
);

CREATE TABLE IF NOT EXISTS dim_customer (
  tenant_id TEXT NOT NULL,
  customer_id TEXT NOT NULL,
  last_event_ts TIMESTAMP WITHOUT TIME ZONE,
  PRIMARY KEY (tenant_id, customer_id)
);

-- CDC log
CREATE TABLE IF NOT EXISTS cdc_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  table_name TEXT NOT NULL,
  row_count INT NOT NULL
);
