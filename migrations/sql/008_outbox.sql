-- 008_outbox.sql
CREATE TABLE IF NOT EXISTS outbox (
  id BIGSERIAL PRIMARY KEY,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  aggregate_type VARCHAR(64) NOT NULL,
  aggregate_id VARCHAR(64) NOT NULL,
  event_type VARCHAR(128) NOT NULL,
  payload JSONB NOT NULL,
  headers JSONB NOT NULL DEFAULT '{}'::jsonb,
  attempts INT NOT NULL DEFAULT 0,
  next_attempt_at TIMESTAMP WITHOUT TIME ZONE NULL
);
CREATE INDEX IF NOT EXISTS idx_outbox_created ON outbox(created_at);

CREATE TABLE IF NOT EXISTS dead_letter (
  id BIGSERIAL PRIMARY KEY,
  failed_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  aggregate_type VARCHAR(64) NOT NULL,
  aggregate_id VARCHAR(64) NOT NULL,
  event_type VARCHAR(128) NOT NULL,
  payload JSONB NOT NULL,
  headers JSONB NOT NULL,
  error TEXT NOT NULL,
  attempts INT NOT NULL
);
