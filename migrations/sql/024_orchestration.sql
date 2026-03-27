-- Orchestration core storage
CREATE TABLE IF NOT EXISTS orchestration_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  type TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  status TEXT NOT NULL, -- queued|processed|failed
  info JSONB
);
CREATE INDEX IF NOT EXISTS idx_orch_status ON orchestration_events(status);
CREATE INDEX IF NOT EXISTS idx_orch_type ON orchestration_events(type);

-- demo campaign events
CREATE TABLE IF NOT EXISTS campaign_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  type TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb
);
