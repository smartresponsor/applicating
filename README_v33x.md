# Smartresponsor v33.x Meta-Bundle
Собрано: 2025-10-09

Включено:
- v33.0 Cognitive Policy Mesh (полный): CRDT LWW Map, ConsensusEngine, API, SQL, Helm, UI.
- v33.1 Mesh Hardening (полный): RBAC, secure API `/mesh/secure/*`, Ingress whitelist/mTLS, audit, TTL.

Деплой:
1) Применить SQL: 057, затем 058.
2) Обновить gateway: включить маршруты `/api/policy/mesh/*` и `/api/policy/mesh/secure/*`.
3) В Helm — применить `ingress-hardening-annotations.yaml` и выставить whitelist/mTLS.
4) Прокинуть роли (mTLS/JWT → `X-Role` маппинг или нативные claims).
5) Подключить outbox в Trust Ledger для финализаций.
