# SmartCloud Edition (v25.0)
*обновлено 2025-10-09*

## Что это
Мультиарендная облачная версия Smartresponsor с авто-биллингом, failover и автопровиженингом.

## API
- `POST /api/cloud/tenant/register` — создать арендатора
- `POST /api/cloud/tenant/limit` — лимиты
- `POST /api/cloud/usage/collect` — usage
- `POST /api/cloud/invoice/generate` — суточная агрегация
- `POST /api/cloud/emit` — события оркестрации
- `GET  /api/cloud/health` — состояние

## K8s
```bash
kubectl apply -f deploy/k8s/
kubectl get pods -n smartcloud
```

## Интеграция
- Использует Billing/Marketplace/Governance из v24.x.
- Tenants изолированы таблицами `smartcloud_*` + `tenant_*`.
