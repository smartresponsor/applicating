# Product Component v2 (Smartresponsor / iSponsor)

Итерация II (Performance-Layer): контракты интерфейсов, read-model, PSR-6 cache (Redis-ready), outbox-ready события.

## Новое
- `Interface\Product\ProductInterface`, `ProductRepositoryInterface`, `ProductServiceInterface`
- `Event\Product\ProductEventInterface` + реализации
- Read-model: `ReadModel\Product\ProductRead`
- Cache: `Cache\Product\ProductCacheProviderInterface`, `ProductCacheService` (PSR-6)
- Обновлённый `Service\Product\ProductService` (реализация интерфейса), репозиторий
- Тесты на контракты/кэш

## Подключение
- DI: свяжите `ProductServiceInterface` → `ProductService` и `ProductRepositoryInterface` → `ProductRepository`.
- Кэш: передайте любой PSR-6 `CacheItemPoolInterface` (например, RedisAdapter от symfony/cache).

