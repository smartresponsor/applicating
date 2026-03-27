# Autonomous Governance Layer (v35.0)
Обновлено: 2025-10-09

## Цель
Свести решения Orchestrator и Mesh в мета-решение, проверить compliance, и автоматически применять безопасные лимиты rollout.

## Поток
1) `/governance/evaluate` — считает `metaScore` и статус compliance за окно.
2) `/governance/enforce` — выставляет canary-процент по правилам.

## Интеграция
- Вызывать из Scheduler перед крупными волнами rollout.
- Писать действия в Trust Ledger (через outbox), чтобы сохранять аудит.
