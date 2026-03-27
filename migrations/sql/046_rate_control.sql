-- Global Rate Control & Quarantine
CREATE TABLE IF NOT EXISTS sys_metrics (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  value NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_sys_metrics_name_ts ON sys_metrics(name, ts DESC);

CREATE TABLE IF NOT EXISTS quarantine (
  id BIGSERIAL PRIMARY KEY,
  kind TEXT NOT NULL, -- ip|tenant
  value TEXT NOT NULL,
  reason TEXT NOT NULL,
  since TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  until TIMESTAMP WITHOUT TIME ZONE
);
CREATE INDEX IF NOT EXISTS idx_quarantine_kind_value ON quarantine(kind, value);
