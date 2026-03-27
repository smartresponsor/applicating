-- Autopilot audit storage
CREATE TABLE IF NOT EXISTS autopilot_events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ts TEXT NOT NULL,
  tenant_id TEXT,
  avg NUMERIC,
  slope NUMERIC,
  predicted NUMERIC,
  decision TEXT,
  action TEXT,
  outcome TEXT,
  details TEXT
);
