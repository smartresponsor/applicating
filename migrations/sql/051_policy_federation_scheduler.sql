-- Federation Scheduler
CREATE TABLE IF NOT EXISTS policy_canary (
  name TEXT NOT NULL,
  version INT NOT NULL,
  percent INT NOT NULL DEFAULT 20,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  PRIMARY KEY (name, version)
);

CREATE TABLE IF NOT EXISTS policy_replica_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  name TEXT NOT NULL,
  version INT NOT NULL,
  peer TEXT NOT NULL,
  status TEXT NOT NULL -- ok|fail
);
CREATE INDEX IF NOT EXISTS idx_policy_replica_events_ts ON policy_replica_events(ts DESC);
