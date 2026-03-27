# Maintenance & CI/CD Guidelines (v18.1)

## QA Targets
- `make qa` → запускает phpstan, rector, ecs, phpunit.
- `make clean` → очищает кеш, старые артефакты.

## CI Workflows
- **ci-smoke.yml** — базовая проверка API `/api/catalog`.
- **sonar.yml** — статический анализ и отчёт в SonarCloud.
- **qa.yml** — ручной запуск полного линтинга/тестов.

## Cron Maintenance
Рекомендуется еженедельно:
```bash
php bin/console audit:prune 180
```

## Next
Подготовка v18.2 (Observability bootstrap + OpenTelemetry exporter).
