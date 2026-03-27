# Security & Compliance (v23.5)
*обновлено 2025-10-09*

## Компоненты
- **PolicyEngine** (RBAC/ABAC) + **TenantIsolationMiddleware** — изоляция арендаторов.
- **AuditTrail** — неотчуждаемый аудит действий.
- **SecretsManager + KeyRotation** — хранение секретов и ротация ключей.
- **ConsentRegistry** — учёт согласий субъектов данных (GDPR).
- **DataRetention** — политики хранения/удаления.
- **DLPScanner** — поиск утечек (email/CC) в payload-е.

## SQL
`029_security_compliance.sql` — `audit_logs`, `access_policies`, `secrets_kv`, `key_rotations`, `consents`, `retention_runs`, `dlp_events`.

## CLI
```bash
bin/sec-policy-apply admin read
bin/sec-audit-dump
bin/sec-rotate-key api/partner
bin/sec-consent tenantA user-1 marketing true
bin/sec-retention-run metrics_90d
bin/sec-dlp-scan webhook "mail me at user@example.com"
```

## Интеграция
- Подключи PolicyEngine в контроллерах `/api/*`.
- Аудитируй sensitive-операции: GuardedActions, Orchestration, Public API.
- DLP-проверку добавь в Webhook ingest.
- Регулярно запускай retention и ротацию ключей (cron/Actions).
