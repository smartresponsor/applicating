# TrustMesh AI Federation (v27.2)
*обновлено 2025-10-09*

## Идея
- Агент каждого арендатора репортит SLA/refund/uptime → считается **TrustScore**.
- TrustScore влияет на федерацию и экономику (скидки/приоритеты).
- SecureBridge подписывает/проверяет сообщения между узлами (в демо — HMAC; в бою — Ed25519).

## API
- `POST /api/trustmesh/score/report` — отправить метрики и получить score.
- `GET  /api/trustmesh/aggregate?tenant=...` — получить последний score.
- `POST /api/trustmesh/bridge/verify` — верификация подписи.

## Экономика
- `TrustAdjustBilling::apply(base)` снижает цену для высоких TrustScore.

## Интеграция
- FabricHub может учитывать TrustScore как вес при агрегировании глобальных обновлений.
