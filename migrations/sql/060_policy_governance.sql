-- Autonomous Governance Layer
CREATE TABLE IF NOT EXISTS policy_governance_actions (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  action TEXT NOT NULL,
  value NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_governance_ts ON policy_governance_actions(ts DESC);
