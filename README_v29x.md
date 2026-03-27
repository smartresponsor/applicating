# Smartresponsor v29.x Meta-Bundle
Собрано: 2025-10-09

Включает:
- **v29.0 Policy Federation** — репликация политик между регионами (peers, queue, подписи).
- **v29.1 Adaptive Learning** — варианты политик (A/B/C), bandit‑распределение, feedback.

## SQL миграции
- 049 — federation: `federation_peers`, `policy_replica_queue`
- 050 — adaptive: `policy_variant`, `policy_assignment`, `policy_feedback`

## Интеграция с существующим SmartPolicy (28.x)
1) Примените 049–050.
2) Включите маршруты:
   - `/api/policy/federation/*`
   - `/api/policy/learning/*`
3) Запустите планировщик репликации (cron/k8s job) для очереди `policy_replica_queue`.
4) Перед PolicyFlow дергайте `/learning/assign` — эффекты варианта подайте в Flow.
5) После бизнес‑событий присылайте `/learning/feedback`.

## Метрики и алерты (добавить в Prometheus/Grafana)
- `policy_replica_queue_depth`
- `policy_variant_trials_total`, `policy_variant_win_rate`
- Коррелируйте deny/allow из PolicyFlow с выбранными вариантами.

## Безопасность
- Подписывайте payload федерации (HMAC, FederationSigner).
- Ограничьте `/push` по IP allowlist/mtls.

