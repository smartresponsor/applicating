# Observability Layer (v23.2)
*обновлено 2025-10-09*

## Что делает
- Запись метрик в `metrics_timeseries`.
- Запись спанов трассировки в `traces` (формат, совместимый с Otel-экспортом).
- Правила алертов `alert_rules` + текущие состояния `alerts`.

## CLI
```bash
bin/obs-metric app_requests_total '{"tenant":"tenantA","path":"/api"}' 1
bin/obs-trace <trace_id> <span_id> "demo_span" 200
bin/obs-alert-test app_error_rate 0.05
```

## Экспортеры
- `Exporter/PrometheusExporter.php` — текстовый формат Prometheus.
- `Exporter/OtelExporter.php` — JSON-структура для Otel.

## SQL
`026_observability.sql` — все таблицы Observability.
