# Security & Compliance Hardening

## Security Headers
Включены: HSTS, CSP (базовая), X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy.

## Per-Account Rate-Limit
Token bucket на пользователя/ключ API (JWT `sub` или `X-API-Key`), хранение — Redis (реализация из итераций IX–X).

## Audit Trail
- `migrations/sql/007_audit_log.sql` — схема.
- `AuditLogger` + `AuditTrailMiddleware` — логирует actor, path, статус, IP, UA, параметры (с PII-маскировкой).
- Команда `audit:prune [days]` — чистка старше N дней (по умолчанию 90).

## Рекомендации
- Настройте CSP под ваш фронтенд (nonce/hashes для inline ресурсов).
- Лимит можно дифференцировать по ролям/планам тарифа.
- Для больших объёмов аудита — используйте партиционирование таблиц по дате.
