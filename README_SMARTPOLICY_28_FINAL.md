# SmartPolicy 28 Final Bundle
Собрано: 2025-10-09

## Состав пакета
- product-suite-v28-meta-bundle.zip — Policy Engine (v28.0) + Policy Flow (v28.1) + Zero‑Trust + connectors
- smartpolicy-v28-cicd.zip — CI/CD (GitHub Actions), Helm, Envoy rate-limit, Prometheus, Grafana, rollout scripts
- gateway-policyflow-integration.zip — Middleware для Gateway, конфиги, Grafana patches
- policyflow-live-integration.zip — Live‑коннекторы (Billing/Rate/Trust), метрики, интеграционный тест

## Порядок внедрения (TL;DR)
1) **DB миграции**: примените 047–048 (Policy Registry/Audit/Flow).
2) **Деплой**: helm upgrade/install из `smartpolicy-v28-cicd`.
3) **Gateway**: подключите middleware из `gateway-policyflow-integration`.
4) **Live‑hooks**: установите коннекторы из `policyflow-live-integration`.
5) **Метрики**: включите `/metrics` и Prometheus scrape; импортируйте Grafana dashboard.
6) **Canary**: раскатка 10% → 50% → 100% с авто‑rollback по error_rate>1% и p95>500ms.

## Smoke‑проверка
- `POST /api/policy/reload` — загрузите `tenant.policy.json` (пример в v28 мета‑пакете).
- `POST /api/policy/flow/run` c policy=`discount.vip` и заголовками `X-Tenant`, `X-Plan`.
- Убедитесь, что:
  - в `billing_adjustments` появилась запись со скидкой,
  - в `rate_desired_limits` — новый лимит,
  - в `trustmesh_scores` — скор обновился,
  - в `/metrics` растут `policy_flow_executions_total`.

## Безопасность
- Включите `ZeroTrustGuardMiddleware` и подписи `X-Signature` (HMAC).
- Секреты храните в K8s Secrets; не коммитьте их в репозиторий.

## Что дальше (29.x)
- Federation of Policy (multi‑region OPA/rego)
- Adaptive policy learning на данных из PolicyFlow
- Полная интеграция с Envoy RateLimit Service и Prometheus AlertManager
