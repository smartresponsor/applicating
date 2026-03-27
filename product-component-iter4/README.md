# Product Component v4 (Reliability Layer)

Добавлено: IdempotencyStore, Retry/Backoff, Dead-letter Queue (DLQ), CLI для rebuild/read replay.

## Состав
- Idempotency: `Idempotency/Product/ProductIdempotency.php`, `Service/Product/ProductIdempotencyStore.php`
- Retry: `Service/Product/ProductRetryHandler.php`
- DLQ: `DeadLetter/Product/ProductDeadLetter.php`
- CLI:
  - `product:read:rebuild`
  - `product:outbox:replay-failed`
- Миграции: `migrations/sql/002_reliability.sql`

Подключение: используйте совместно с v3 (Outbox, ReadModel). DI-связи не ломаются.
