#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

command -v php >/dev/null || { echo "PHP not found"; exit 1; }

if [ ! -d vendor ]; then
  command -v composer >/dev/null || { echo "Composer not found"; exit 1; }
  echo "[bootstrap] Installing PHP dependencies..."
  composer install
fi

echo "[bootstrap] Applying Doctrine migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "[bootstrap] Loading Applicating demo fixtures..."
php bin/console applicating:fixtures:load-demo --no-interaction

echo "[bootstrap] Bootstrap complete."
echo "[bootstrap] Start the app with one of the following commands:"
echo "  symfony server:start -d"
echo "  php -S 127.0.0.1:8000 -t public"
echo "[bootstrap] Health endpoint: http://127.0.0.1:8000/health"
