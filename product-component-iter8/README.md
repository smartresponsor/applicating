# Product Component v8 (HTTP / OpenAPI Layer)

Готовый REST-слой для каталога:
- Symfony маршруты (attributes) + конфиг в `config/routes/product_catalog.yaml`
- DTO: `CatalogQueryDTO`, `CursorDTO`
- Контроллер: `ApiProductCatalogController` (`/api/catalog`)
- OpenAPI-аннотации (совместимо со swagger-php/NelmioApiDoc)
- E2E-smoke тест (скелет)

Зависимости (ожидаемые в проекте):
- symfony/framework-bundle ^7
- symfony/routing ^7
- symfony/http-foundation ^7
- symfony/validator ^7 (опционально для аннотаций)
- doctrine/dbal ^3 (т.к. адаптер каталога использует Connection)
