# Async Queue Bootstrap (v18.3)

Компоненты: OutboxRelay → Queue, Consumer (retry/backoff), DeadLetterHandler.

## Запуск
docker compose -f docker/docker-compose.async.yml up -d

## Миграции
psql $PGURL -f migrations/sql/008_outbox.sql

## Использование
см. классы в `src/Async/Queue/`.
