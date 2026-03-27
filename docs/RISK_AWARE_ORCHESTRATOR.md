# Risk-Aware Policy Orchestrator (v34.0)
Обновлено: 2025-10-09

## Идея
Перед применением политики оцениваем риск (riskScore), доверие региона (trustScore) и потенциальный эффект (impact).
Далее принимается одно из решений: apply | hold | reject.

## API
- `POST /api/policy/orchestrator/evaluate` — вычисляет risk/trust/final и выдаёт решение.

## Интеграция
- Federation Scheduler и Mesh могут вызывать Orchestrator перед финализацией/rollout.
- Решение логируется в `policy_orchestrator_decisions` и может использоваться Intelligence Hub.
