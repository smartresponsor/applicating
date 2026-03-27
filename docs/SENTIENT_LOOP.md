# Sentient Intelligence Loop (v36.0)
Обновлено: 2025-10-09

## Идея
Цикл, который собирает обратную связь, предсказывает аномалии, обучает пороги и автоматически корректирует risk/trust конфигурацию.

## API
- `POST /api/intelligence/feedback` — сбор обратной связи
- `GET  /api/intelligence/predict?window=60` — прогнозы по SLA/latency
- `POST /api/intelligence/train` — обучение порогов
- `POST /api/intelligence/adjust` — запись новых порогов

## Интеграция
- Webhook'и в Orchestrator/Governance для применения новых порогов.
- CronJob `smartpolicy-sentient-loop` — автоматическая тренировка и коррекция.
