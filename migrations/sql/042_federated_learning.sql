-- Federated Learning Protocol
CREATE TABLE IF NOT EXISTS flp_sync_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  anon_tenant TEXT NOT NULL,
  payload TEXT NOT NULL,
  signature TEXT NOT NULL,
  direction TEXT NOT NULL -- in|out
);
