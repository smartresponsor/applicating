-- Predictive pipelines storage (demo)
CREATE TABLE IF NOT EXISTS stream_outbox (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  topic TEXT NOT NULL,
  tenant_id TEXT NOT NULL,
  type TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  status TEXT NOT NULL -- queued|sent
);
CREATE INDEX IF NOT EXISTS idx_stream_topic_status ON stream_outbox(topic, status);

CREATE TABLE IF NOT EXISTS forecast_results (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  model TEXT NOT NULL,
  target TEXT NOT NULL,
  predicted NUMERIC,
  actual NUMERIC,
  error NUMERIC,
  feedback_score NUMERIC
);
CREATE INDEX IF NOT EXISTS idx_forecast_tenant_target_ts ON forecast_results(tenant_id, target, ts DESC);
