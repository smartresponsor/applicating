-- System Integrity & Audit Layer
CREATE TABLE IF NOT EXISTS system_audit_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  source TEXT NOT NULL,
  event TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  hash TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_system_audit_log_ts ON system_audit_log(ts DESC);

CREATE TABLE IF NOT EXISTS system_audit_ledger (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  type TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  prev_hash TEXT,
  hash TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_system_audit_ledger_ts ON system_audit_ledger(ts DESC);
