-- TrustMesh Federation
CREATE TABLE IF NOT EXISTS trustmesh_reports (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  sla NUMERIC NOT NULL,
  refund_rate NUMERIC NOT NULL,
  uptime NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_trustmesh_reports_tenant_ts ON trustmesh_reports(tenant_id, ts DESC);

CREATE TABLE IF NOT EXISTS trustmesh_scores (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  score NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_trustmesh_scores_tenant_ts ON trustmesh_scores(tenant_id, ts DESC);
