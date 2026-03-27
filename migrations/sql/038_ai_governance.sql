-- AI Governance & Predictive Scaling
CREATE TABLE IF NOT EXISTS telemetry_qps (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  qps NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_tqps_tenant_ts ON telemetry_qps(tenant_id, ts DESC);

CREATE TABLE IF NOT EXISTS telemetry_errors (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  error_rate NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_terrs_tenant_ts ON telemetry_errors(tenant_id, ts DESC);
