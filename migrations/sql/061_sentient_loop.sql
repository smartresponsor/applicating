-- Sentient Intelligence Loop
CREATE TABLE IF NOT EXISTS sentient_feedback (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  region TEXT NOT NULL,
  sla_ok NUMERIC NOT NULL,
  latency_ms NUMERIC NOT NULL,
  risk NUMERIC NOT NULL,
  trust NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_sentient_feedback_ts ON sentient_feedback(ts DESC);

CREATE TABLE IF NOT EXISTS sentient_updates (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  payload JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_sentient_updates_ts ON sentient_updates(ts DESC);
