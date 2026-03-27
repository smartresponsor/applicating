# Federated Learning Protocol (v27.1)
*обновлено 2025-10-09*

## Состав
- `FederatedSyncService` — сбор локальных весов, шум, подпись, логирование.
- `DiffCompressor` — сжатие diff.
- `PrivacyLayer` — анонимизация и шум.
- `SignatureService` — HMAC-подпись пакетов.
- `FederatedAPI` — `/api/neural/fabric/sync`, `/api/neural/fabric/receive`.
- Hooks: `GlobalAdjustApplier` (Governance), `GlobalAdjustBilling` (Billing).

## Поток
1) `sync(tenant)` → формируется outbound-пакет (anon_tenant + diff) + `signature` → лог `out`  
2) другой узел → `receive(payload, signature)` → проверка подписи → лог `in` → запись в `neural_fabric_updates`  
3) Governance/Billing читают `global_adjust` и применяют скидки/лимиты.

## Безопасность
- В демо — HMAC-SHA256 (в бою → Ed25519/ECDSA).  
- Differential Privacy через небольшое добавление шума.  
- tenant_id не раскрывается, вместо него — хэш.

## Интеграция
- SmartCloud/AI может запускать sync по cron или событиям (инвойс, спайк QPS).
