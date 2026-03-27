-- Self-healing audit storage
CREATE TABLE IF NOT EXISTS healing_events (
  id BIGSERIAL PRIMARY KEY,
  alertname TEXT NOT NULL,
  summary TEXT,
  action TEXT NOT NULL,
  outcome TEXT,
  details JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_healing_created ON healing_events(created_at);
