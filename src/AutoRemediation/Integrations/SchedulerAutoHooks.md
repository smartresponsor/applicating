# Scheduler Auto-Hooks
- STOP_ROLLOUT: вызов `/api/policy/federation/scheduler/tick` с `overridePercent=0`
- RESUME_ROLLOUT: `overridePercent=20`
- SCALE_SCHEDULER: интеграция с Helm/K8s (HPA/ReplicaSet) — заглушка реализуется в CI/CD
