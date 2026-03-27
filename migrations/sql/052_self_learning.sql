-- Self-Learning Federation
CREATE TABLE IF NOT EXISTS federation_learning_log (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  policy TEXT NOT NULL,
  regions JSONB NOT NULL DEFAULT '[]'::jsonb
);
