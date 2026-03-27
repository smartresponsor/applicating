-- Risk-Aware Policy Orchestrator
CREATE TABLE IF NOT EXISTS policy_orchestrator_decisions (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  region TEXT NOT NULL,
  risk_score NUMERIC NOT NULL,
  trust_score NUMERIC NOT NULL,
  impact_score NUMERIC NOT NULL,
  final_score NUMERIC NOT NULL,
  decision TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_orchestrator_ts ON policy_orchestrator_decisions(ts DESC);
