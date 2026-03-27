# Pricing Engine (v22.1)
*обновлено 2025-10-09*

## Что делает
- Динамический расчёт цены с учётом эластичности спроса и гвардов (min margin, floor/ceil, caps).
- A/B-тестирование вариаций.
- Экспорт в Stripe (заглушка).

## CLI
```bash
bin/pricing-run tenantA SKU-001 10 -1.2 1.15 0.3 6 7 25 gold
```

## SQL
- `pricing_rules` — базовые параметры.
- `price_tests` — метрики A/B.
- `price_events` — аудит изменений цен.
