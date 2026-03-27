# Predictive Pipelines (v23.4)
*обновлено 2025-10-09*

## Поток
ETL/Orchestration → Kafka (smart.events) → ForecastWorker → forecast_results → Feedback → retrain

## Компоненты
- `Producer`/`Consumer` — обёртки над брокером (в демо — таблица `stream_outbox`).
- `ForecastWorker` — вычисляет простой прогноз (+5%) и пишет в `forecast_results`.
- `FeedbackHandler` — записывает фактическое значение, считает ошибку/оценку.
- `PipelineManager` — publish + runOnce (batch).

## SQL
`028_predictive_pipelines.sql` — `stream_outbox`, `forecast_results`.

## CLI
```bash
bin/stream-produce tenantA forecast.request 120
bin/stream-consume
bin/stream-feedback tenantA revenue 110
bin/forecast-train
```

## Дальше
- Подключить настоящий клиент Kafka/Redpanda.
- Добавить авто-эмит событий в Orchestration после прогноза.
