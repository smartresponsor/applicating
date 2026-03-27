-- Priority Queues + Traffic Shaping
CREATE TABLE IF NOT EXISTS traffic_queue (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  task TEXT NOT NULL,
  priority INT NOT NULL,
  status TEXT NOT NULL, -- queued|processing|done|failed
  started_at TIMESTAMP WITHOUT TIME ZONE,
  finished_at TIMESTAMP WITHOUT TIME ZONE
);
CREATE INDEX IF NOT EXISTS idx_tq_status_prio_ts ON traffic_queue(status, priority DESC, ts ASC);
