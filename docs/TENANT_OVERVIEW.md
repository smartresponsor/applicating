# Distributed Tenants & Sharding (v23.1)
*обновлено 2025-10-09*

## Назначение
Горизонтальное масштабирование через регионы и шарды (PostgreSQL, Redis, ClickHouse).

## Компоненты
- `TenantRegistry` — реестр арендаторов (DSN, регион).
- `ShardManager` — выбор шарда (хеш-распределение).
- `TenantResolver` — определение арендатора по хосту/заголовку.
- `ConnectionPool` — пул соединений для динамического роутинга.
- `PgShardConnector`/`RedisShardConnector`/`ClickhouseWriter` — низкоуровневые клиенты.
- `MigrationHelper` — упрощённый раннер SQL.

## SQL
Схемы: `tenant_registry`, `tenant_shards` (`025_tenant_sharding.sql`).

## CLI
```bash
CLI/tenant-migrate
CLI/tenant-create tenantA us-east pdo-pgsql://user:pass@host/db
CLI/tenant-shardmap us-east
```

## Интеграция
- Резолвинг `tenant_id` → выбор DSN → подключение к нужному шару.
- Кэширование роутинга в Redis (рекомендовано).
- ClickHouse — для больших аналитических таблиц ABI/SLA/CLA.
