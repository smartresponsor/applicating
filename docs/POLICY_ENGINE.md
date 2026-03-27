# Policy-as-Code Engine (v28.0)
*обновлено 2025-10-09*

## Идея
- Все правила — в версиях, хранятся в `policy_registry`.
- Выполнение через `PolicyEngine`: условия `when`, проверки `allow/deny`, побочные эффекты `effect`.
- Zero-Trust Guard чистит заголовки и проверяет подписи.

## API
- `POST /api/policy/reload` — загрузить политики (json/yaml/rego).
- `POST /api/policy/eval` — выполнить в контексте {tenant,qps,trustscore,role,...}.

## Интеграция
- Gateway/Billing/AI Governance вызывают `/api/policy/eval` перед критическими действиями.
