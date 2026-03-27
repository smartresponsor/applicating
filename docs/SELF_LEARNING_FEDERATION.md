# Self-Learning Federation (v30.0)
Обновлено: 2025-10-09

## Поток
1) Регион A экспортирует агрегированные метрики варианта политики (`/export`).
2) Регион B применяет обновление (`/apply`) — обновляются alpha/beta у локальных policy_variant.
3) Scheduler учитывает улучшения при rollout'е следующих версий.

## Метрики
- `federation_learning_updates_total` (можно считать по INSERT в `federation_learning_log`)
- `cross_region_sync_latency` (разница времени экспорта/применения)

## Безопасность
- Подпись HMAC для payload (MetricsSigner).
- Желательно mtls/IP allowlist для `/apply`.
