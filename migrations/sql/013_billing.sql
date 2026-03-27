-- 013_billing.sql

CREATE TABLE IF NOT EXISTS billing_plans (
  id TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  monthly_price_cents INT NOT NULL,
  currency TEXT NOT NULL DEFAULT 'USD',
  rate_limit_per_minute INT NOT NULL,
  monthly_quota_requests INT NOT NULL,
  features JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  plan_id TEXT NOT NULL REFERENCES billing_plans(id),
  stripe_customer_id TEXT,
  stripe_subscription_id TEXT,
  status TEXT NOT NULL DEFAULT 'active', -- active/canceled/past_due/paused
  current_period_start TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  current_period_end TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW() + INTERVAL '30 days',
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_subscriptions_tenant ON subscriptions(tenant_id);

CREATE TABLE IF NOT EXISTS usage_events (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  occurred_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  kind TEXT NOT NULL, -- api_request, job_run, etc
  quantity INT NOT NULL DEFAULT 1,
  meta JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_usage_tenant_period ON usage_events(tenant_id, occurred_at);

CREATE TABLE IF NOT EXISTS invoices (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  period_start TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  period_end TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  subtotal_cents INT NOT NULL,
  tax_cents INT NOT NULL DEFAULT 0,
  total_cents INT NOT NULL,
  currency TEXT NOT NULL DEFAULT 'USD',
  stripe_invoice_id TEXT,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_invoices_tenant ON invoices(tenant_id);

-- Helper view: monthly usage per tenant
CREATE OR REPLACE VIEW monthly_usage AS
SELECT tenant_id,
       date_trunc('month', occurred_at) AS month,
       SUM(quantity) AS requests
FROM usage_events
GROUP BY tenant_id, date_trunc('month', occurred_at);
