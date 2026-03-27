# Observability & Security Extension (v19.0)

## OpenTelemetry (Extended)
- Подключи `HttpTracingMiddleware` к событиям Symfony: `kernel.request` → `onRequest`, `kernel.response` → `onResponse`.
- Используй `DbTracing::trace('query', fn()=> $db->executeStatement(...))` вокруг критичных запросов.
- Экспорт идёт через Collector (см. v18.2 пакет).

## Vault
- Конфиг: `config/vault.yaml` → addr, token.
- Методы: `read(path)`, `write(path, data)`, `renewSelf()`.
- Рекомендуется включить auto-renew по cron/k8s `CronJob`.

## RBAC Scopes
- Правила в `config/rbac_scopes.yaml`, middleware `ScopeEnforcer` проверяет наличие скопа в атрибуте запроса.
- Связывается с предыдущим уровнем API Keys (v18.4), где у ключа есть массив `scopes`.

## Audit Sink → S3/MinIO
- `S3AuditSinkService::dumpDay($day)` создаёт `*.ndjson.gz` по дню.
- Загрузка через CLI (`aws s3 cp` или `mc cp`) — задаётся `S3_SYNC_CMD`.
- План: запустить по расписанию (k8s CronJob) на ежедневную выгрузку.

## Makefile (пример)
```
vault-test:
	php -r "require 'vendor/autoload.php'; (new App\\Component\\Product\\Integration\\Vault\\VaultClient(getenv('VAULT_ADDR')?:'http://localhost:8200', getenv('VAULT_TOKEN')?:'root'))->renewSelf(); echo 'ok';"

audit-sync:
	php -r "$d=new DateTimeImmutable('yesterday'); require 'vendor/autoload.php'; $svc=new App\\Component\\Product\\Audit\\S3AuditSinkService($db,'s3://my-bucket'); $f=$svc->dumpDay($d); echo $f, PHP_EOL;"
```

## Workflows
- `.github/workflows/vault-health.yml` — проверяет `/v1/sys/health`.
