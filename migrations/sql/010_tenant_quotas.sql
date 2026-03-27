-- 010_tenant_quotas.sql
CREATE TABLE IF NOT EXISTS tenant_quotas (
  tenant_id VARCHAR(64) PRIMARY KEY,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  request_limit INT NOT NULL,
  request_used INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
