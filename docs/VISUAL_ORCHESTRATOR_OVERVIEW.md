# Visual Orchestrator (v23.6)
*обновлено 2025-10-09*

## Что делает
- Граф: узлы (emit, llm.run, webhook.send, guard.enqueue, api.call) + связи.
- Проверка схемы (валидатор) и выполнение в топологическом порядке.
- Хранение схем в `orchestrations_schema`.

## CLI
```bash
bin/orch-graph '{{"name":"demo","nodes":[{{"id":"n1","type":"emit","params":{{"type":"abi.forecast.ready","payload":{{}}}}}}],"edges":[]}}'
bin/orch-exec  '{{"name":"demo","nodes":[{{"id":"n1","type":"emit","params":{{"type":"abi.forecast.ready","payload":{{"slope":0.2}}}}}},{{"id":"n2","type":"llm.run","params":{{"advisor":"AdvisorPricing","prompt":"auto"}}}}],"edges":[{{"from":"n1","to":"n2"}}]}}' tenantA
```

## REST (план)
- GET /api/orch/graph/{{id}} — получение схемы
- POST /api/orch/exec/{{id}} — запуск
- POST /api/orch/graph/import — импорт JSON
