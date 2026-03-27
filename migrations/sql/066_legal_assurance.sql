-- Legal Contracts & Assurance Mesh
CREATE TABLE IF NOT EXISTS legal_templates (
  id BIGSERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  body TEXT NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_legal_templates_name ON legal_templates(name);

CREATE TABLE IF NOT EXISTS legal_contracts (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  body TEXT NOT NULL,
  context JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_legal_contracts_ts ON legal_contracts(ts DESC);

CREATE TABLE IF NOT EXISTS legal_assurance_checks (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  rules JSONB NOT NULL DEFAULT '{}'::jsonb,
  result JSONB NOT NULL DEFAULT '{}'::jsonb
);
