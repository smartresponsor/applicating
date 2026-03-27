# Smartresponsor v28.x Meta-Bundle
Собрано: 2025-10-09

Включает релизы:
- **v28.0 Policy-as-Code Engine**
- **v28.1 Policy Flow Orchestrator**
- Zero‑Trust Guard, connectors Billing/Trust/Rate
- SQL миграции 047–048

## Архитектура
- Policy Engine управляет политиками (evaluate, audit, registry)
- Policy Flow превращает их в workflow, связывая TrustMesh и RateControl
- Zero‑Trust Guard обеспечивает подписи и безопасные контексты

## Взаимодействие с другими компонентами
- **TrustMesh (v27.2):** Policy Flow использует `TrustConnector` для извлечения trustscore.
- **RateControl (v27.5):** Policy Flow применяет rate-политику и может ограничивать QPS через `RateConnector`.
- **Billing (v25.x):** через `BillingConnector` автоматически создаются скидки и записи об изменении тарифов.
- **Governance:** политики позволяют централизованно регулировать доступ, лимиты и скидки.

## Интеграция
1. Импортируйте SQL миграции 047–048.
2. Подключите `PolicyEngine` и `PolicyFlowEngine` к DI.
3. Включите маршруты `/api/policy/*` и `/api/policy/flow/*`.
4. Разрешите cron/CI `policy_engine.yml` и `policy_flow.yml`.
5. Используйте `ZeroTrustGuard` для подписей API‑запросов.

## Дорожная карта 28.x → 29.x
- Policy federation: распределённая проверка между регионами.
- Policy learning: адаптивные веса на основе результатов Flow.
- Full trust‑governed LLM layer для автономных решений.
