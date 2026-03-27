-- Auto-Remediation Log
CREATE TABLE IF NOT EXISTS policy_remediation_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  action TEXT NOT NULL,
  status TEXT NOT NULL,
  details TEXT
);
CREATE INDEX IF NOT EXISTS idx_policy_remediation_ts ON policy_remediation_log(ts DESC);
