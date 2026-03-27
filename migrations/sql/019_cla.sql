-- Customer Lifecycle Automation storage
CREATE TABLE IF NOT EXISTS customer_lifecycle_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  customer_id TEXT NOT NULL,
  stage TEXT NOT NULL,
  churn_score NUMERIC NOT NULL,
  campaign TEXT NOT NULL,
  channel TEXT NOT NULL,
  details JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_cle_tenant ON customer_lifecycle_events(tenant_id);
CREATE INDEX IF NOT EXISTS idx_cle_customer ON customer_lifecycle_events(customer_id);
