-- Compliance Nexus
CREATE TABLE IF NOT EXISTS compliance_mapping (
  id BIGSERIAL PRIMARY KEY,
  standard TEXT NOT NULL,
  key TEXT NOT NULL,
  value JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_compliance_mapping_std ON compliance_mapping(standard);

CREATE TABLE IF NOT EXISTS compliance_consent (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  subject_id TEXT NOT NULL,
  purpose TEXT NOT NULL,
  granted INT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_compliance_consent_subject ON compliance_consent(subject_id);

CREATE TABLE IF NOT EXISTS compliance_retention (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  dataset TEXT NOT NULL,
  ttl_days INT NOT NULL,
  status TEXT NOT NULL,
  deleted_at TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS compliance_dpia (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  process TEXT NOT NULL,
  risk_score NUMERIC NOT NULL,
  factors JSONB NOT NULL DEFAULT '{}'::jsonb
);
