-- Cognitive Policy Mesh
CREATE TABLE IF NOT EXISTS policy_mesh_proposals (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  topic TEXT NOT NULL,
  payload JSONB NOT NULL DEFAULT '{}'::jsonb,
  region TEXT NOT NULL,
  status TEXT NOT NULL,
  applied_at TIMESTAMP WITHOUT TIME ZONE
);
CREATE INDEX IF NOT EXISTS idx_policy_mesh_proposals_ts ON policy_mesh_proposals(ts DESC);

CREATE TABLE IF NOT EXISTS policy_mesh_votes (
  id BIGSERIAL PRIMARY KEY,
  ts TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  proposal_id BIGINT NOT NULL,
  region TEXT NOT NULL,
  vote TEXT NOT NULL,
  FOREIGN KEY (proposal_id) REFERENCES policy_mesh_proposals(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_policy_mesh_votes_pid ON policy_mesh_votes(proposal_id);
