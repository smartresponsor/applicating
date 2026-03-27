# AI Governance & Predictive Cloud Scaling (v26.0)
*обновлено 2025-10-09*

## Что делает
- Прогнозирует QPS и предлагает число реплик (autoscale).
- Рекомендует месячные лимиты по данным расходов и ошибок.
- Находит аномалии usage (последний час vs среднее 24ч) и может переписывать политику.

## API
- `POST /api/ai/autoscale` — желаемые реплики
- `GET  /api/ai/recommend/limit?tenant=...` — рекомендованный лимит
- `GET  /api/ai/scan?tenant=...` — скан аномалий
- `POST /api/ai/policy/apply` — применить авто-политику

## Интеграция
- Работает поверх SmartCloud (v25.x), использует таблицы `tenant_usage`, `gov_policies` и телеметрию из `telemetry_*`.
