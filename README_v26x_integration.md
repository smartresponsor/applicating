# v26.x Integration Roadmap (Smartresponsor)
*обновлено 2025-10-09*

Эта папка содержит дорожную карту и шаблоны сервисов для v26.1–v26.5.

## Этапы
- **v26.1 Telemetry Bridge** — сбор QPS/Errors/Latency, API `/api/telemetry/report`.
- **v26.2 AI Feedback Loop** — подписка на события, очередь `ai_feedback_queue`, переписывание policy.
- **v26.3 Cloud Autopilot** — интеграция с K8s, autoscaler per tenant.
- **v26.4 Adaptive Governance** — AI Trust + суточный sync ACL.
- **v26.5 Global Dashboard** — единая консоль AI/Telemetry/Policy.

Смотрите соответствующие директории `src/*` и `docs/architecture/*.plantuml`.
