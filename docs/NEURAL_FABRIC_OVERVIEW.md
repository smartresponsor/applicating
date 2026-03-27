# Neural Cloud Fabric (v27.0)
*обновлено 2025-10-09*

## Идея
- У каждого арендатора есть **TenantAgent**, который принимает локальные решения (adjust).
- **FabricHub** усредняет локальные решения и делает **глобальные** обновления.
- **TrainingPipeline** хранит локальные веса (демо).

## API
- `POST /api/neural/agent/decide {tenant}`
- `POST /api/neural/fabric/aggregate`
- `POST /api/neural/train/save {tenant,w}`

## Интеграция
- Решения агентов могут влиять на Governance/Billing (через events или прямой вызов).
- Fabric broadcast записывается в `tenant_events` (type=`fabric.broadcast`).

## Следующие шаги
- Замена заглушек на реальные модели (LLM/ML).
- Безопасный федеративный обмен (подписи, дифф-обновления).
