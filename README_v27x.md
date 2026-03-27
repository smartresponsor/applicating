# Smartresponsor v27.x Meta-Bundle
Собрано: 2025-10-09

Включены релизы:
- v27.0 Neural Cloud Fabric
- v27.1 Federated Learning Protocol
- v27.2 TrustMesh Federation
- v27.3 Priority Queues + Traffic Shaping
- v27.4 Anti‑DDoS & Abuse Guard
- v27.5 Global Rate Control & Quarantine

## Содержимое
В каталоге лежат индивидуальные архивы релизов (zip). Разворачивайте по мере необходимости.

## Установка (общая идея)
1. Примените SQL миграции по порядку 041..046.
2. Подключите middleware (AbuseGuard, RateGuard) в Gateway.
3. Включите Telemetry/AI/FLP/TrustMesh кроны/воркфлоу.
4. Синхронизируйте лимиты с Envoy Rate Limit Service.

## Дорожная карта 27.x → 28.x
- Реальные модели (LLM/ML) вместо заглушек, безопасные ключи и Ed25519.
- Интеграция с Prometheus/Envoy/Redis в прод-режиме.
- Policy-as-code для Governance (OPA/Rego), канареечные обновления.
