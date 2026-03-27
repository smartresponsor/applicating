-- Adaptive Policy Learning (bandits + feedback)
CREATE TABLE IF NOT EXISTS policy_variant (
  id BIGSERIAL PRIMARY KEY,
  policy TEXT NOT NULL,
  variant_id TEXT NOT NULL,
  effects JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  alpha NUMERIC NOT NULL DEFAULT 1.0,
  beta  NUMERIC NOT NULL DEFAULT 1.0,
  wins INT NOT NULL DEFAULT 0,
  trials INT NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_policy_variant ON policy_variant(policy, variant_id);

CREATE TABLE IF NOT EXISTS policy_assignment (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  variant TEXT NOT NULL,
  tenant_id TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_assignment_policy_ts ON policy_assignment(policy, ts DESC);

CREATE TABLE IF NOT EXISTS policy_feedback (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  variant TEXT NOT NULL,
  tenant_id TEXT NOT NULL,
  reward NUMERIC NOT NULL,
  context JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_policy_feedback_policy_ts ON policy_feedback(policy, ts DESC);
