#!/usr/bin/env bash
set -euo pipefail
# Apply SQL migrations from iterations (001..005 and 002,003,004 where present)
PGURL="${PGURL:-postgresql://app:app@localhost:5432/app}"
apply_sql() { f="$1"; echo "Applying $f"; psql "$PGURL" -v ON_ERROR_STOP=1 -f "$f"; }

for f in   product-component-iter3/migrations/sql/001_init_product.sql   product-component-iter4/migrations/sql/002_reliability.sql   product-component-iter5/migrations/sql/003_catalog.sql   product-component-iter6/migrations/sql/004_catalog_map.sql   product-component-iter7/migrations/sql/005_denorm_and_cursor.sql ; do
  if [ -f "$f" ]; then apply_sql "$f"; else echo "Skip $f (not found)"; fi
done
