# Observability Bootstrap (v18.2)

## Stack
- **OTel Collector**: OTLP receiver (4317 gRPC / 4318 HTTP). Exports traces → Jaeger, logs → Loki.
- **Jaeger**: http://localhost:16686
- **Grafana**: http://localhost:3000 (admin/admin), add Loki datasource http://loki:3100
- **Loki**: http://localhost:3100

## Run
```bash
docker compose -f docker/docker-compose.yml -f docker/docker-compose.otel.yml up -d
export OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
export OTEL_TRACES_SAMPLER_RATIO=0.2
```

## PHP Instrumentation
1) Установить зависимости (в вашем проекте):
```
composer require open-telemetry/sdk open-telemetry/exporter-otlp monolog/monolog
```
2) В раннем bootstrap вашего приложения:
```php
\App\Component\Product\Observability\Tracing\TracingBootstrap::boot();
$tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('catalog');
$span = $tracer->spanBuilder('bootstrap')->startSpan();
$span->end();
```
3) Логи через Monolog:
```php
$logger = new \Monolog\Logger('app');
$logger->pushHandler(new \App\Component\Product\Observability\Logging\OtlpHttpLogHandler(getenv('OTEL_EXPORTER_OTLP_LOGS_ENDPOINT') ?: 'http://localhost:4318/v1/logs'));
// $logger->info('catalog started', ['route'=>'/api/catalog']);
```

## Verify
- Traces: открой Jaeger UI → сервис `catalog` → увидишь спаны.
- Logs: Grafana → добавь Loki datasource → explore `{service_name="catalog"}`.

## Env Vars
- `OTEL_EXPORTER_OTLP_ENDPOINT` (default `http://localhost:4318`)
- `OTEL_TRACES_SAMPLER_RATIO` (0.0..1.0)
- `OTEL_EXPORTER_OTLP_LOGS_ENDPOINT` (default `$OTEL_EXPORTER_OTLP_ENDPOINT/v1/logs`)
