-- Trust Ledger (append-only)
CREATE TABLE IF NOT EXISTS trust_ledger (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  prev_hash TEXT NOT NULL,
  payload TEXT NOT NULL,
  signature TEXT NOT NULL,
  hash TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_trust_ledger_ts ON trust_ledger(ts DESC);
CREATE UNIQUE INDEX IF NOT EXISTS uq_trust_ledger_hash ON trust_ledger(hash);
