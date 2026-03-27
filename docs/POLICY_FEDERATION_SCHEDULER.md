# Policy Federation Scheduler (v29.2)
*обновлено 2025-10-09*

## Что делает
- Выбирает задачи из `policy_replica_queue` и рассылает на peers партиями (canary).
- Записывает события в `policy_replica_events`.
- Метрики: `policy_replica_queue_depth`, `policy_replication_failures_total`.

## API
- `POST /api/policy/federation/scheduler/tick` — один тик планировщика.

## Cron
Запускайте тик каждую минуту (K8s CronJob или systemd timer).

## Настройки
- `batchPercent` — доля пиров в одной партии (по умолчанию 20%).
- `timeoutSec` — таймаут push-запроса.
