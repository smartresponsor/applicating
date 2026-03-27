-- Policy Intelligence Hub: snapshot table
CREATE TABLE IF NOT EXISTS policy_intelligence_snapshot (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  window_min INT NOT NULL,
  replication_failures_5xx INT NOT NULL,
  replication_queue_depth INT NOT NULL,
  predictive_avg_score NUMERIC NOT NULL,
  ledger_blocks_appended INT NOT NULL,
  policy_variant_trials_total INT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_intel_ts ON policy_intelligence_snapshot(ts DESC);
