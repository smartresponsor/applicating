# Multi‑Tenant Core (v20.0)

Поддерживаются два режима изоляции:
- **RLS** (Row-Level Security, один schema `public`, колонка `tenant_id`) — проще миграции, дешевле операционно.
- **Schema-per-tenant** (отдельный schema на арендатора) — лучшая изоляция/производительность, сложнее управление.

## Миграции
```bash
psql "$PGURL" -f migrations/sql/011_tenant_core.sql
```

## Symfony интеграция
- `TenantResolverMiddleware` — читает `X-Tenant-Id`, прокидывает в контекст.
- `TenantDbRouter` — на запросе устанавливает `SET LOCAL app.tenant_id = <tenant>` (RLS) или `SET LOCAL search_path TO <tenant>, public` (schema mode).

Подключение (пример):
```yaml
# services.yaml
App\Component\Product\Tenant\TenantContext: ~
App\Component\Product\Tenant\Http\TenantResolverMiddleware:
  arguments: ['@App\Component\Product\Tenant\TenantContext', { mode: '%env(TENANT_MODE)%' }]
App\Component\Product\Tenant\Doctrine\TenantDbRouter:
  arguments: ['@doctrine.dbal.default_connection','@App\Component\Product\Tenant\TenantContext']
```

## Provision
- `TenantProvisioner::createTenant(id, name, mode)` — регистрирует арендатора, создаёт схему (в schema-mode).
- `seedProducts(tenant, items, mode)` — наполняет демо-данными в выбранном режиме.

## Рекомендации
- Начни с **RLS**, затем для крупных арендаторов мигрируй в **schema-per-tenant** (гибридный режим поддержан).
- Добавь метрики per-tenant (`tenant_id` label) и лимиты (см. v18.4 quotas).
- Для schema-per-tenant — планируй миграции как fan-out (mig job по списку tenants).
