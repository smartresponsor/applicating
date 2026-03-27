# Smart Policy Orchestrator (v28.1)
*обновлено 2025-10-09*

## Что делает
- Конвертирует политику (id) из `policy_registry` в workflow.
- Вызывает коннекторы (Trust, Rate, Billing) и пишет логи.

## API
- `POST /api/policy/flow/run {policy, tenant, plan, qps, trustscore}`
- `GET  /api/policy/flow/status`
