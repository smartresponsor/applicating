CREATE TABLE IF NOT EXISTS billing_usage_log (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  product_id BIGINT NOT NULL,
  units NUMERIC(18,6) NOT NULL CHECK (units >= 0),
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  trace_id TEXT,
  recorded_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  sent_to_provider BOOLEAN NOT NULL DEFAULT FALSE,
  provider_response JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_billing_usage_tenant ON billing_usage_log(tenant_id);
CREATE INDEX IF NOT EXISTS idx_billing_usage_product ON billing_usage_log(product_id);

CREATE TABLE IF NOT EXISTS billing_stripe_map (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  product_id BIGINT NOT NULL,
  subscription_item_id TEXT NOT NULL,
  UNIQUE(tenant_id, product_id)
);

CREATE TABLE IF NOT EXISTS billing_invoice (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  provider TEXT NOT NULL,  -- 'stripe'
  external_id TEXT,        -- stripe invoice id
  status TEXT NOT NULL DEFAULT 'pending',
  total NUMERIC(18,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
