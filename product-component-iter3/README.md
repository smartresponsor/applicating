# Product Component v3 (Smartresponsor / iSponsor)

Итерация III (Transactional Layer): Outbox + Idempotency, SQL миграции, Redis config примеры, Factory/Mapper, расширенные тесты.

## Новое
- Outbox: `Outbox\Product\ProductOutbox` (+ publisher) и консольная команда `OutboxRelayCommand`
- Миграции: `migrations/sql/001_init_product.sql`
- Конфиги: `config/services_product.yaml`, `config/cache_product.yaml` (Redis пример)
- Фабрики/мапперы: `Factory\Product\ProductFactory`, `Mapper\Product\ProductMapper`
- Обновлённый `Service\Product\ProductService` — транзакционное сохранение + запись событий в Outbox

## Быстрый старт
1. Примените SQL миграции из `migrations/sql/001_init_product.sql`
2. Подключите конфиги (правьте DSN Redis под окружение)
3. Сопоставьте интерфейсы в DI: `ProductServiceInterface -> ProductService`, `ProductRepositoryInterface -> ProductRepository`

