-- Governance Layer
CREATE TABLE IF NOT EXISTS gov_policies (
  id BIGSERIAL PRIMARY KEY,
  scope TEXT NOT NULL, -- tenantId|global
  name TEXT NOT NULL,
  rules JSONB NOT NULL DEFAULT '{}'::jsonb,
  version INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_gov_policies_scope ON gov_policies(scope, created_at DESC);

CREATE TABLE IF NOT EXISTS gov_roles (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  user_id TEXT NOT NULL,
  role TEXT NOT NULL,
  assigned_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_gov_roles ON gov_roles(tenant_id, user_id);

CREATE TABLE IF NOT EXISTS gov_trust (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  plugin_id INT NOT NULL,
  score INT NOT NULL,
  by_user TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS gov_proposals (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT NOT NULL,
  title TEXT NOT NULL,
  body TEXT NOT NULL,
  author TEXT NOT NULL,
  status TEXT NOT NULL, -- open|closed
  result TEXT,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  closed_at TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS gov_votes (
  id BIGSERIAL PRIMARY KEY,
  proposal_id INT NOT NULL,
  user_id TEXT NOT NULL,
  choice TEXT NOT NULL, -- yes|no|abstain
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS gov_enforcements (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  tenant_id TEXT NOT NULL,
  action TEXT NOT NULL,
  context JSONB NOT NULL DEFAULT '{}'::jsonb,
  result TEXT NOT NULL
);
