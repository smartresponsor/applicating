# Reliability Playbook (Catalog)

## SLI/SLO
- **Availability SLO**: 99.9% за 30 дней.
- **Latency SLO**: P99 ≤ 1s, P95 ≤ 300ms.
- **Error rate**: 5xx ≤ 0.1% от общего трафика.

### Экспортеры/Метрики
Используйте стандартные `http_requests_total` и `http_request_duration_seconds_bucket` (Prometheus).
Если свой middleware — экспортируйте счетчики `catalog_api_requests_total` и гистограммы `catalog_api_latency_seconds`.

## Алертинги
- Fast burn (10–30 мин окно) и slow burn (6–12 ч) для error budget.
- Отдельное правило для P99 latency.

## Grafana
Добавьте три дашборда из `monitoring/grafana/*.json`.

## Chaos
- `PodChaos`: раз в неделю убиваем под — проверяем auto-heal и readiness.
- `NetworkChaos`: еженедельно в понедельник — задержка 200±50ms на 2 минуты (наблюдаем деградацию).

## Миграции без даунтайма
- Вначале — **backfill** и **dual writes** (если меняете модель).
- Пример Job — в `migrations/zero-downtime/zdt-job.yaml` (для MySQL/gh-ost; для PostgreSQL используйте `pg-osc`).
- Обязательно — фаза **verification** и откат.

## Принципы
- Везде включайте **idempotency**, **retry/backoff**, **DLQ** (есть в итерации IV).
- При релизах — используйте **Argo Rollouts** (итерация XIV) и наблюдайте **canary vs stable**.

