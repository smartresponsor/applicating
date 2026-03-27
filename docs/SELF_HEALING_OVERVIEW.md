# Self-Healing Engine (v23.7)
*обновлено 2025-10-09*

## Что делает
- Сканирует аномалии (metrics/alerts), создаёт IncidentReport.
- Подбирает действия (HealingRules): retry/restart/rollback/disable/notify.
- Выполняет RecoveryExecutor и пишет результат в `healing_actions`.

## CLI
```bash
bin/heal-scan tenantA
bin/heal-run tenantA
bin/heal-report
```

## SQL
`031_self_healing.sql` — таблицы `healing_incidents`, `healing_actions`.
