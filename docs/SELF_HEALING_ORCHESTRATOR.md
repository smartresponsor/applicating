# Self‑Healing Actions Orchestrator (v21.2)
*updated 2025-10-08*

## Компоненты
- `KubernetesClient` — безопасный wrapper для `kubectl -n <ns> ...`.
- `ActionOrchestrator::apply(action, ctx)` — выполняет действие и пишет аудит в `healing_events`.
- CLI: `bin/heal-apply-action`, `bin/hpa-patch`, `bin/rollback`, `bin/circuit`.

## Поддерживаемые действия
- `restart` — `kubectl rollout restart deployment/<name>`
- `scale-up / scale-down` — `kubectl scale deployment ... --replicas=N`
- `hpa-patch` — patch HPA min/max/cpu
- `helm-rollback` — откат релиза через helm runner pod
- `circuit-breaker-on/off` — включает/выключает метку `traffic=blocked` на deployment

## Интеграция с Policy Engine
Политика (v21.1) → high-level решение (scale-up/scale-down/rollback/...)  
Маппинг хранится в `config/action_policies.yaml` → конкретное действие orchestrator + контекст.

## Примеры
```bash
bin/heal-apply-action restart '{"deployment":"catalog-api","alertname":"HighErrorRate"}'
bin/heal-apply-action hpa-patch '{"hpa":"app-hpa","min":3,"max":10,"cpu":60}'
bin/rollback smartresponsor 12 helm-runner
bin/circuit app on
```
