# Smartresponsor Product Suite — INSTALL

## Requirements
- PHP 8.2+
- PostgreSQL 15+/Redis 7+
- Composer, Make, Docker (optional)

## 0) Клонируй/распакуй
Скопируй содержимое пакета в корень проекта или `composer require smartresponsor/product-suite-installer` (итерация XI).

## 1) Инфра (локально)
```bash
make up
export PGURL=postgresql://app:app@localhost:5432/app
```

## 2) Миграции
Применить SQL (001..007). Если используешь meta-package v1–v10, можно:
```bash
make migrate
```
Или вручную:
```bash
psql "$PGURL" -f migrations/sql/001_init_product.sql
psql "$PGURL" -f migrations/sql/002_reliability.sql
psql "$PGURL" -f migrations/sql/003_catalog.sql
psql "$PGURL" -f migrations/sql/004_catalog_map.sql
psql "$PGURL" -f migrations/sql/005_denorm_and_cursor.sql
psql "$PGURL" -f migrations/sql/006_product_v2.sql
psql "$PGURL" -f migrations/sql/007_audit_log.sql
```

## 3) DI wiring (Symfony)
Смотри `examples/symfony/services.yaml` и `config/routes/product_catalog.yaml`. Минимум:
- Зарегистрируй адаптер `ProductCatalogAdapter` и контроллер `ApiProductCatalogController`.
- Подключи middleware: `JwtAuthMiddleware`, `RateLimitMiddleware`/`PerAccountRateLimitMiddleware`, `SecurityHeadersMiddleware`, `ExceptionSubscriber`, `AuditTrailMiddleware`.
- Добавь `RedisTokenBucketStore` и `JwtHelper`.

## 4) HTTP API
- эндпоинты `/api/catalog`, `/api/catalog/{id}`, `/api/catalog/facets` (итерации VII–VIII).
- OpenAPI: `openapi/catalog.yaml` или `bin/generate-openapi` (итерации IX–X).

## 5) Прод
- Helm чарты (`helm/catalog`), CI/CD (`.github/workflows/{test,release,cd}.yml`).
- ArgoCD/Rollouts (итерация XIV), мониторинг и SLO/SLI (XV).
- Схема v2 без даунтайма (XVI).

## 6) Check-list перед продом
- [ ] DB мигрирована (001..007)
- [ ] JWT секрет и rate-limit через Redis
- [ ] OpenAPI сгенерирован и задеплоен в Swagger UI
- [ ] Алерты/дашборды в Prometheus/Grafana
- [ ] Артефакты CI/CD проходят ✅
