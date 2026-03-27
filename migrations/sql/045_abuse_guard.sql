-- Anti-DDoS & Abuse Guard
CREATE TABLE IF NOT EXISTS abuse_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  ip TEXT NOT NULL,
  tenant_id TEXT NOT NULL,
  action TEXT NOT NULL,
  status INT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_abuse_events_ts_ip ON abuse_events(ts DESC, ip);

CREATE TABLE IF NOT EXISTS abuse_bans (
  id BIGSERIAL PRIMARY KEY,
  kind TEXT NOT NULL, -- ip|tenant
  value TEXT NOT NULL,
  reason TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  expires_at TIMESTAMP WITHOUT TIME ZONE
);
CREATE INDEX IF NOT EXISTS idx_abuse_bans_kind_value ON abuse_bans(kind, value);
