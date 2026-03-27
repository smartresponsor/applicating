-- 009_api_keys.sql
CREATE TABLE IF NOT EXISTS api_keys (
  id BIGSERIAL PRIMARY KEY,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id VARCHAR(64) NOT NULL,
  key_hash VARCHAR(128) NOT NULL,
  name VARCHAR(128) NOT NULL,
  scopes TEXT[] NOT NULL DEFAULT ARRAY[]::TEXT[],
  roles TEXT[] NOT NULL DEFAULT ARRAY['VIEWER']::TEXT[],
  rate_limit_per_minute INT NOT NULL DEFAULT 120,
  active_until TIMESTAMP WITHOUT TIME ZONE NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  UNIQUE(tenant_id, key_hash)
);
CREATE INDEX IF NOT EXISTS idx_api_keys_tenant ON api_keys(tenant_id);
