# Self-Healing Controller (v21.0)
*updated 2025-10-08*

## Возможности
- Принимает вебхуки Alertmanager (Prometheus).
- В таблицу `healing_events` сохраняет: alertname, summary, action, timestamp.
- Возможные действия: restart / scale-up / noop.

## Пример вебхука
```json
{ "alerts": [{"labels": {"alertname": "HighCPUUsage"}, "annotations": {"summary": "CPU > 90%"} }] }
```

## Настройка
Webhook: `/api/ai/self-healing`  
Подключить в Alertmanager:
```yaml
receivers:
  - name: self-healing
    webhook_configs:
      - url: http://app/api/ai/self-healing
```
