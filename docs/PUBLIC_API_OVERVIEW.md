# Public API + Webhooks (v22.7)
*обновлено 2025-10-09*

## Что есть
- **API-ключи** (`api_keys`), простейший **rate-limit** (окно 1 мин).
- Эндпоинты:  
  - `GET /api/public/v1/abi/forecast?tenant=...`  
  - `GET /api/public/v1/cla/churn?tenant=...`  
  - `GET /api/public/v1/pricing/price?tenant=...&sku=...`
- **Webhooks** с HMAC-подписью:  
  - `POST /api/public/v1/webhooks/ingest` (header `X-Signature: sha256=...`)

## OpenAPI
`src/PublicAPI/OpenAPI/openapi.yaml` — можно импортировать в Swagger / Postman.

## CLI
```bash
bin/api-create-key tenantA "partner-app"
bin/webhook-sign dev_secret '{"source":"partner","type":"event","data":{}}'
```

## SQL
`023_public_api.sql` — таблицы `api_keys`, `ratelimit`, `webhook_events`.
