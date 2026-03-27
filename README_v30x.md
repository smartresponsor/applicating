# Smartresponsor v30.x Meta-Bundle
Собрано: 2025-10-09

Включает:
- v29 Meta (Federation + Adaptive) — база.
- v29.2 Federation Scheduler — canary rollout и события.
- v30.0 Self-Learning Federation — обмен метриками и глобальная адаптация.

## Деплой поверх v28.x
1) Примените миграции: 049, 050, 051, 052.
2) Включите API маршруты:
   - /api/policy/federation/*
   - /api/policy/learning/*
   - /api/policy/federation/scheduler/*
   - /api/policy/federation/learning/*
3) Cron/K8s: запускать scheduler tick каждую минуту.
4) Cross-region: периодически выполнять export/apply (см. Helm Jobs).
5) Наблюдение: добавить панели replication/learning в Grafana и алерты по queue_depth/latency.
