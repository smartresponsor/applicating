# Product Component v10 (Production-grade Integration)

Включено:
- JWT аутентификация (middleware + helper)
- Redis rate-limit store (`RedisTokenBucketStore`) + конфиг
- ExceptionSubscriber с нормализацией ошибок и trace_id
- События API (`ApiRequestEvent`, `ApiErrorEvent`) + Prometheus exporter `/metrics`
- Скрипт генерации OpenAPI (`bin/generate-openapi`) — собирает YAML из аннотаций
- Набросок нагрузочного теста (Artillery)

Совместимо с предыдущими итерациями (v7–v9). Подключение опционально и не ломает контракт.
