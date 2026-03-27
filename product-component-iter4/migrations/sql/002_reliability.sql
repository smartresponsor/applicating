-- migrations/sql/002_reliability.sql
CREATE TABLE IF NOT EXISTS product_idempotency (
  key_hash CHAR(64) PRIMARY KEY,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  processed BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS product_dead_letter (
  id UUID PRIMARY KEY,
  event_name VARCHAR(128) NOT NULL,
  payload TEXT NOT NULL,
  error TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  retries INT NOT NULL DEFAULT 0,
  last_retry_at TIMESTAMP WITHOUT TIME ZONE NULL
);
