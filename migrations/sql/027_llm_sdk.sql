-- LLM Assistants SDK
CREATE TABLE IF NOT EXISTS assistant_decisions (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  advisor TEXT NOT NULL,
  prompt TEXT NOT NULL,
  decision JSONB NOT NULL,
  score NUMERIC NOT NULL,
  source TEXT NOT NULL,
  applied BOOLEAN NOT NULL DEFAULT FALSE
);
CREATE INDEX IF NOT EXISTS idx_ad_tenant_ts ON assistant_decisions(tenant_id, ts DESC);
