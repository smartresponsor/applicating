-- Self-Healing storage
CREATE TABLE IF NOT EXISTS healing_incidents (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  component TEXT NOT NULL,
  status TEXT NOT NULL, -- firing|resolved
  error TEXT NOT NULL,
  rule_applied TEXT,
  healed BOOLEAN NOT NULL DEFAULT FALSE,
  retries INT NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_heal_tenant_ts ON healing_incidents(tenant_id, ts DESC);

CREATE TABLE IF NOT EXISTS healing_actions (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  component TEXT NOT NULL,
  action TEXT NOT NULL,
  params JSONB NOT NULL DEFAULT '{}'::jsonb,
  result TEXT NOT NULL,
  rule_applied TEXT
);
