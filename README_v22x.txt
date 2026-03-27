# Smartresponsor Product Suite v22.x

## Состав релиза
| Версия | Компонент | Описание |
|--------|------------|-----------|
| v22.0 | ABI Layer | Аналитика и прогноз бизнес-метрик |
| v22.1 | Pricing Engine | Эластичность спроса, A/B-прайсинг |
| v22.2 | SLA Engine | SLA/кредиты/стоимость SLO |
| v22.3 | CLA | Жизненный цикл клиента, churn, winback |
| v22.4 | Dashboard | Панель KPI и графиков ABI/CLA |
| v22.5 | ETL Connectors | Загрузка usage/billing/CRM и CDC |
| v22.6 | Guarded Actions | Подтверждения рискованных действий |
| v22.7 | Public API | API v1, Webhooks, HMAC, OpenAPI |

## Установка
1. Распаковать архивы в `/src` проекта (каждый компонент из `product-suite-...`).
2. Применить SQL из `migrations/sql` по порядку.
3. Настроить `.env` и `DATABASE_URL`.
4. Проверить CLI (`bin/...`) и cron workflows `.github/workflows/...`.

## Минимальные требования
- PHP 8.3+, Doctrine ORM, Symfony 6+
- PostgreSQL 14+
- Node 18+ для Admin UI
- GitHub Actions (или cron) для CI

## Документация
Каждый компонент содержит `docs/XXX_OVERVIEW.md`.
