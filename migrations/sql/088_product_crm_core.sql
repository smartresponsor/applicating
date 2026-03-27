
-- Product CRM Core (v35.0)
-- Пайплайны, стадии, сделки, активности, история переходов

CREATE TABLE IF NOT EXISTS crm_pipeline (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT,
  product_id BIGINT,
  name TEXT NOT NULL,
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS ux_crm_pipeline_default
  ON crm_pipeline(tenant_id, product_id)
  WHERE is_default = TRUE;

CREATE TABLE IF NOT EXISTS crm_stage (
  id BIGSERIAL PRIMARY KEY,
  pipeline_id BIGINT NOT NULL REFERENCES crm_pipeline(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  position INT NOT NULL,
  is_win BOOLEAN NOT NULL DEFAULT FALSE,
  is_lost BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE UNIQUE INDEX IF NOT EXISTS ux_crm_stage_pos
  ON crm_stage(pipeline_id, position);

CREATE TABLE IF NOT EXISTS crm_deal (
  id BIGSERIAL PRIMARY KEY,
  tenant_id TEXT,
  product_id BIGINT,
  title TEXT NOT NULL,
  amount NUMERIC,
  currency TEXT,
  status TEXT NOT NULL DEFAULT 'open', -- open|won|lost
  pipeline_id BIGINT NOT NULL REFERENCES crm_pipeline(id) ON DELETE RESTRICT,
  stage_id BIGINT NOT NULL REFERENCES crm_stage(id) ON DELETE RESTRICT,
  owner_user_id BIGINT,
  customer_ref TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ,
  close_at TIMESTAMPTZ,
  lost_reason TEXT
);

CREATE INDEX IF NOT EXISTS ix_crm_deal_tenant_product ON crm_deal(tenant_id, product_id);
CREATE INDEX IF NOT EXISTS ix_crm_deal_status ON crm_deal(status);
CREATE INDEX IF NOT EXISTS ix_crm_deal_stage ON crm_deal(stage_id);

CREATE TABLE IF NOT EXISTS crm_deal_stage_history (
  id BIGSERIAL PRIMARY KEY,
  deal_id BIGINT NOT NULL REFERENCES crm_deal(id) ON DELETE CASCADE,
  from_stage_id BIGINT,
  to_stage_id BIGINT NOT NULL,
  actor_user_id BIGINT,
  moved_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS crm_activity (
  id BIGSERIAL PRIMARY KEY,
  deal_id BIGINT NOT NULL REFERENCES crm_deal(id) ON DELETE CASCADE,
  type TEXT NOT NULL,   -- note|call|email|meeting|task
  content JSONB,        -- свободная схема
  due_at TIMESTAMPTZ,
  done_at TIMESTAMPTZ,
  created_by BIGINT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Простая воронка по активным сделкам за 30 дней
CREATE OR REPLACE VIEW vw_crm_funnel_30d AS
SELECT s.name AS stage,
       COUNT(d.id) AS deals,
       COALESCE(SUM(d.amount),0) AS amount_sum
FROM crm_deal d
JOIN crm_stage s ON s.id = d.stage_id
WHERE d.created_at >= NOW() - INTERVAL '30 days' AND d.status='open'
GROUP BY s.name
ORDER BY MAX(s.position);
