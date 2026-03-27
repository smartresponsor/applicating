-- Policy Federation
CREATE TABLE IF NOT EXISTS federation_peers (
  id BIGSERIAL PRIMARY KEY,
  region TEXT NOT NULL,
  endpoint TEXT NOT NULL,
  pubkey TEXT,
  added_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS uq_federation_peers_region ON federation_peers(region);

CREATE TABLE IF NOT EXISTS policy_replica_queue (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  version INT NOT NULL,
  status TEXT NOT NULL, -- queued|sending|done|failed
  started_at TIMESTAMP WITHOUT TIME ZONE,
  finished_at TIMESTAMP WITHOUT TIME ZONE,
  error TEXT
);
CREATE INDEX IF NOT EXISTS idx_policy_replica_status_ts ON policy_replica_queue(status, ts);
