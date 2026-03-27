-- Plugin Marketplace
CREATE TABLE IF NOT EXISTS marketplace_plugins (
  id BIGSERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  author TEXT NOT NULL,
  latest_version TEXT NOT NULL,
  price_usd NUMERIC NOT NULL DEFAULT 0,
  permissions JSONB NOT NULL DEFAULT '{}'::jsonb,
  meta JSONB NOT NULL DEFAULT '{}'::jsonb,
  published_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_marketplace_name ON marketplace_plugins(name);

CREATE TABLE IF NOT EXISTS marketplace_releases (
  id BIGSERIAL PRIMARY KEY,
  plugin_id INT NOT NULL,
  version TEXT NOT NULL,
  manifest_json TEXT NOT NULL,
  signature TEXT NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS tenant_plugins (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  plugin_id INT NOT NULL,
  version TEXT NOT NULL,
  status TEXT NOT NULL, -- installed|removed
  installed_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_tenant_plugins ON tenant_plugins(tenant_id, plugin_id);

CREATE TABLE IF NOT EXISTS plugin_events (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  plugin_id INT NOT NULL,
  event TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb
);
