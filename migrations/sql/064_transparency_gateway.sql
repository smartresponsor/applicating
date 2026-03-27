-- Transparency Gateway cache (optional)
CREATE TABLE IF NOT EXISTS transparency_cache (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  payload JSONB NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS idx_transparency_cache_ts ON transparency_cache(ts DESC);
