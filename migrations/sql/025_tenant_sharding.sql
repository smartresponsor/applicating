-- Tenant registry & shards
CREATE TABLE IF NOT EXISTS tenant_registry (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL UNIQUE,
  region TEXT NOT NULL,
  pg_dsn TEXT NOT NULL,
  redis_dsn TEXT NOT NULL,
  clickhouse_dsn TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS tenant_shards (
  id BIGSERIAL PRIMARY KEY,
  region TEXT NOT NULL,
  db_host TEXT NOT NULL,
  db_name TEXT NOT NULL,
  shard_key_range TEXT,
  weight INT NOT NULL DEFAULT 1
);
