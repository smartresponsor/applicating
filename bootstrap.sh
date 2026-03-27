#!/usr/bin/env bash
set -euo pipefail

echo "[bootstrap] Checking dependencies..."
command -v docker >/dev/null || { echo "Docker not found"; exit 1; }
command -v make >/dev/null || { echo "Make not found"; exit 1; }
command -v php >/dev/null || { echo "PHP not found"; exit 1; }

echo "[bootstrap] Starting containers (Postgres + Redis)..."
make up

echo "[bootstrap] Applying migrations..."
make migrate || true

echo "[bootstrap] Seeding demo data..."
php scripts/demo_seed.php || psql postgresql://app:app@localhost:5432/app -f migrations/sql/demo_seed.sql || true

echo "[bootstrap] Starting API on http://localhost:8080"
php -S 0.0.0.0:8080 -t public &

echo "[bootstrap] Done. Open http://localhost:8080/api/catalog"
