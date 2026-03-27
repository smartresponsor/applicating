# Predictive Governance (v31.0)
Обновлено: 2025-10-09

## Идея
Перед rollout новой политики — смоделировать ожидаемый эффект и дать итоговый **score**.
Если score ниже порога — остановить rollout/отправить на доработку.

## API
- `POST /api/policy/predictive/whatif {policy, effects(json), runs}` → симуляция и score
- `POST /api/policy/predictive/mlscore {trust, discount, rate_limit}` → ML-оценка конверсии

## Интеграция с Federation Scheduler
- До отправки в `policy_replica_queue` — дернуть `/predictive/whatif`.
- Если score < порога (например, 0.55), не ставить задачу в очередь, записать причину в Ledger.
