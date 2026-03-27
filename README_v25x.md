# Smartresponsor Product Suite v25.x (SmartCloud Edition)

## Основное назначение
SmartCloud Edition — это облачная мультиарендная версия Smartresponsor, интегрирующая:
- Автоматическую оркестрацию арендаторов (Orchestrator, TenantService)
- Автобиллинг и usage-агрегацию (BillingDaemon)
- Автопровиженинг (AutoProvisioner)
- K8s/Envoy интеграцию из серии 24.x
- Governance и Marketplace совместимы с Cloud Tenant API

## Архитектура
```
[Gateway] → [SmartCloud Orchestrator]
                ↓
           [TenantService]
                ↓
          [BillingDaemon] → [Invoices]
                ↓
          [Governance Layer]
```

## API Обзор
| Метод | Назначение |
|--------|------------|
| POST /api/cloud/tenant/register | создать арендатора |
| POST /api/cloud/tenant/limit | лимиты и план |
| POST /api/cloud/usage/collect | учёт usage |
| POST /api/cloud/invoice/generate | суточный биллинг |
| POST /api/cloud/emit | события оркестрации |
| GET /api/cloud/health | состояние SmartCloud |

## Kubernetes
```
kubectl apply -f deploy/k8s/
kubectl get pods -n smartcloud
```

## Интеграция с 24.x
- Billing и Marketplace переиспользуются без изменений.
- Governance Layer применяет ACL для Cloud tenants.
- DeveloperHub позволяет публиковать плагины с tenant-aware конфигурацией.


Собрано: 2025-10-09
