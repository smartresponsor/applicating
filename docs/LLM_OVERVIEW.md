# LLM Assistants SDK (v23.3)
*обновлено 2025-10-09*

## Назначение
Подключение LLM-агентов как плагинов, анализ контекста ABI/CLA/SLA, генерация решений и логирование в `assistant_decisions`.

## Состав
- `PluginInterface`, `PluginManager`, `ContextBuilder`, `DecisionLogger`, `AssistantAPI`.
- Встроенные плагины: `AdvisorPricing`, `AdvisorChurn`, `AdvisorSLA`.

## SQL
`027_llm_sdk.sql` — таблица `assistant_decisions`.

## CLI
```bash
bin/assistant-list
bin/assistant-run tenantA "increase revenue"
```

## Интеграция с Orchestration
Решения из `assistant_decisions` можно забирать воркером и транслировать в события `/api/orch/emit`.
