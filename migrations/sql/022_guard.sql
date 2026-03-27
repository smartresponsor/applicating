-- Guarded Actions approval queue
CREATE TABLE IF NOT EXISTS approval_requests (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  action TEXT NOT NULL,
  params JSONB NOT NULL DEFAULT '{}'::jsonb,
  risk_level TEXT NOT NULL,
  status TEXT NOT NULL, -- pending|approved|rejected
  requested_by TEXT NOT NULL,
  approved_by TEXT,
  ts_request TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  ts_approve TIMESTAMP WITHOUT TIME ZONE,
  details JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_approval_tenant ON approval_requests(tenant_id);
CREATE INDEX IF NOT EXISTS idx_approval_status ON approval_requests(status);
