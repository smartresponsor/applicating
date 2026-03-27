-- AI Trust Predictions
CREATE TABLE IF NOT EXISTS trust_predictions (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  plugin_id INT NOT NULL,
  tenant_id TEXT,
  score NUMERIC NOT NULL,
  features JSONB NOT NULL DEFAULT '{}'::jsonb
);
