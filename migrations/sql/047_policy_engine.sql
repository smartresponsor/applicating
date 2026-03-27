-- Policy-as-Code Engine
CREATE TABLE IF NOT EXISTS policy_registry (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  version INT NOT NULL,
  format TEXT NOT NULL, -- yaml|json|rego
  content TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_policy_registry_name_ts ON policy_registry(name, ts DESC);

CREATE TABLE IF NOT EXISTS policy_audit (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  subject TEXT NOT NULL,
  decision TEXT NOT NULL, -- allow|deny
  context JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_policy_audit_name_ts ON policy_audit(name, ts DESC);
