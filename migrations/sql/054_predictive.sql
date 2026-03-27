-- Predictive Governance
CREATE TABLE IF NOT EXISTS policy_simulation_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  effects JSONB NOT NULL DEFAULT '{}'::jsonb,
  runs INT NOT NULL,
  expected_reward NUMERIC NOT NULL,
  expected_deny_rate NUMERIC NOT NULL,
  expected_latency_ms NUMERIC NOT NULL,
  score NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_sim_log_policy_ts ON policy_simulation_log(policy, ts DESC);
