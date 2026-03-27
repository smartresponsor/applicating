#!/usr/bin/env bash
set -euo pipefail
PGURL="${PGURL:-postgresql://app:app@localhost:5432/app}"
echo "Applying component migrations (001..005 if available)…"
for f in migrations/sql/*.sql; do
  [ -f "$f" ] && psql "$PGURL" -v ON_ERROR_STOP=1 -f "$f" || true
done
