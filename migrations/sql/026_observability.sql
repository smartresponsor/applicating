-- Observability storage
CREATE TABLE IF NOT EXISTS metrics_timeseries (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  labels JSONB NOT NULL DEFAULT '{}'::jsonb,
  value NUMERIC NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_metrics_name_ts ON metrics_timeseries(name, ts);

CREATE TABLE IF NOT EXISTS traces (
  id BIGSERIAL PRIMARY KEY,
  trace_id TEXT NOT NULL,
  span_id TEXT NOT NULL,
  parent_id TEXT,
  name TEXT NOT NULL,
  start_ts TIMESTAMP WITHOUT TIME ZONE NOT NULL,
  duration_ms INT NOT NULL,
  attributes JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_traces_trace ON traces(trace_id);

CREATE TABLE IF NOT EXISTS alert_rules (
  id BIGSERIAL PRIMARY KEY,
  key TEXT NOT NULL UNIQUE,
  expr TEXT NOT NULL,
  severity TEXT NOT NULL,
  enabled BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS alerts (
  id BIGSERIAL PRIMARY KEY,
  rule_key TEXT NOT NULL,
  status TEXT NOT NULL, -- firing|resolved
  started_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  resolved_at TIMESTAMP WITHOUT TIME ZONE,
  labels JSONB NOT NULL DEFAULT '{}'::jsonb,
  annotations JSONB NOT NULL DEFAULT '{}'::jsonb
);
