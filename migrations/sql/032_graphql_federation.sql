-- GraphQL Federation registry
CREATE TABLE IF NOT EXISTS gql_subgraphs (
  id BIGSERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  url TEXT NOT NULL,
  sdl TEXT NOT NULL,
  version TEXT NOT NULL,
  registered_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_gql_subgraphs_name ON gql_subgraphs(name);
