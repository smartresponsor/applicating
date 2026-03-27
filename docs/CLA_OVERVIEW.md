# Customer Lifecycle Automation (v22.3)
*обновлено 2025-10-09*

## Что делает
- Определяет стадию клиента (new/active/at_risk/lost).
- Считает churn score.
- Выбирает кампанию и канал (email/webhook), записывает событие.

## CLI
```bash
bin/cla-run tenantA cust-001 10 45 2 40 0 user@example.com https://example.tld/hook
```

## Хранилище
Таблица `customer_lifecycle_events`: ts, tenant_id, customer_id, stage, churn_score, campaign, channel, details.
