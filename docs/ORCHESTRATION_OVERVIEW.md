# Orchestration Layer (v23.0)
*обновлено 2025-10-09*

## Что делает
- Принимает события (ABI/CLA/SLA/ETL) → хранит в `orchestration_events`.
- Сопоставляет с `TriggerRegistry` и проверяет правила `RuleEngine`.
- Выполняет действия `ActionExecutor` (pricing.adjust, campaign.winback, credit.issue).
- **GuardBridge**: risky-действия автоматически ставятся в `approval_requests` (medium/high).
- API: `POST /api/orch/emit` и `POST /api/orch/run`.

## CLI
```bash
bin/orch-emit abi.forecast.ready tenantA 'slope'
bin/orch-run
```

## Таблицы
- `orchestration_events` — события и статус обработки.
- `campaign_events` — демо-действия кампаний.
