# Data Connectors & ETL (v22.5)
*обновлено 2025-10-09*

## Источники
- **usage_json** — пакетные JSON-файлы (`tenant_id`, `ts`, `metric`, `value`)
- **billing_csv / stripe** — CSV или Stripe API (инвойсы)
- **crm_webhook** — события поведения клиента

## Конвейер
staging_* → facts/dims → CDC log → дашборд ABI/CLA

## Таблицы
- staging_usage, staging_billing, staging_crm
- fact_usage_daily, fact_billing, dim_customer
- cdc_log

## CLI
```bash
bin/etl-usage var/ingest/usage/sample.json
bin/etl-billing csv tenantA var/ingest/billing/sample.csv
bin/etl-crm
bin/etl-cdc
```

## Конфигурация
`config/etl.yaml` — пути источников и параметры загрузки.
