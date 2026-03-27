# Distributed Deployment (v24.4)
*обновлено 2025-10-09*

## Состав
- `deploy/k8s/*` — манифесты Deployment/Service/Ingress
- `deploy/envoy/envoy.yaml` — маршрутизация к gateway
- `src/Deployment/ServiceHealth.php` — запись статуса в БД
- `bin/deploy-check`, `bin/envoy-validate` — утилиты
- `.github/workflows/deploy_k8s.yml` — CI заготовка

## Быстрый старт
```bash
kubectl apply -f deploy/k8s/
kubectl get pods -n smartresponsor
./bin/envoy-validate
```

## Масштабирование
```bash
kubectl scale deploy orchestrator -n smartresponsor --replicas=3
```
