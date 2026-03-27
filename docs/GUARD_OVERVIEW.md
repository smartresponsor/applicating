# Guarded Actions (v22.6)
*обновлено 2025-10-09*

## Для чего
Добавляет ручное подтверждение для рискованных действий ABI/CLA (повышение цены, изменение SLA, массовые кампании).

## Схема
- `GuardRegistry` — регистр правил (условие → риск → требует ли approval).
- `ApprovalQueue` — постановка заявки в очередь.
- `ApprovalService` — approve/reject и выполнение действия после утверждения.
- `ApprovalRequest` — сущность заявки.

## SQL
Таблица `approval_requests`: tenant_id, action, params, risk_level, status, requested_by, approved_by, ts_request, ts_approve, details.

## CLI
```bash
bin/guard-list tenantA
bin/guard-approve <id> [admin@example.com]
bin/guard-reject <id> [admin@example.com] "reason"
```
