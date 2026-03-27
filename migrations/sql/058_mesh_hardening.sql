-- Mesh Hardening: audit + rbac (minimal tables)
CREATE TABLE IF NOT EXISTS policy_mesh_audit (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  proposal_id BIGINT NOT NULL,
  result JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_policy_mesh_audit_ts ON policy_mesh_audit(ts DESC);
