#!/bin/sh
set -e
php bin/console doctrine:migrations:migrate --no-interaction
psql "$PGURL" -f config/sql/catalog_pg_ltree.sql || true
mysql "$MYSQL_URL" < config/sql/catalog_mysql_infra_category.sql || true
