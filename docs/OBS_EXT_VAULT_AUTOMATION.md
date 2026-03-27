# Observability Extended & Vault Automation (v19.1)

## CronJobs
- **audit-sync** — ежедневно выгружает audit_log в S3.
- **vault-renew** — продлевает Vault токен каждые 30 минут.

## Helm Values
- `helm/values-audit.yaml` и `helm/values-vault.yaml` — параметры CronJob'ов.

## Symfony Integration
- `KernelEventSubscriber.php` подключает `HttpTracingMiddleware` и `ScopeEnforcer` к событиям ядра.
- `services_observability.yaml` регистрирует подписчик событий.

## GitHub Actions
`.github/workflows/audit-rotation.yml` — пример автоматического применения CronJob через kubectl.
