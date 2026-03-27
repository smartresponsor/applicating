# Smartresponsor v30.x — Meta Final
Собрано: 2025-10-09

## Состав
- v30 meta bundle (Federation+Adaptive+Scheduler+Self-Learning)
- v30.1 Trust Ledger (append-only, HMAC)
- Helm patches: learning jobs, ledger verify
- GitOps: Flux/ArgoCD манифесты
- Дополнительно: umbrella-helm, Terraform (если присутствуют)

## Порядок включения
1) Применить миграции: 049, 050, 051, 052, 053.
2) Обновить umbrella chart до v30 и подключить **learning jobs** и **ledger verify CronJob**.
3) Подключить GitOps (Flux/ArgoCD) к каталогу `helm/patches` с `values-ledger.yaml`.
4) Проверить `/api/trust-ledger/verify?depth=1000` и алерты по queue_depth/failures.
5) Включить append-on-events в Scheduler и Federation (после replicate/apply — POST в `/api/trust-ledger/append`).

## Безопасность
- Хранить `ledger.secret` и HMAC ключи в K8s Secrets.
- Ограничить доступ к `/append` (mtls/ip allowlist).

