# Product Component (Smartresponsor / iSponsor)

Независимый доменный модуль **Product**: сущности, VO, сервис, события, репозиторий, минимальные тесты.

## Что внутри
- `Entity\Product` — агрегат Товар
- `Entity\ProductI18n` — мультиязычные поля (name, slug, description)
- `Enum\ProductStatus` — DRAFT/ACTIVE/ARCHIVED
- `ValueObject\Sku`, `ValueObject\Money` — простые VO
- `DTO\ProductCreateDTO`, `DTO\ProductUpdateDTO`
- `Service\ProductService` — create/update/changePrice/adjustStock (+ доменные события)
- `Event\*` — ProductCreated, ProductUpdated, PriceChanged, StockAdjusted
- `Repository\ProductRepository` — Doctrine репозиторий

## Требования
- PHP 8.2+
- doctrine/orm ^2.18
- symfony/uid ^7.0
- psr/event-dispatcher ^1.0

## Быстрый старт
```bash
composer require smartresponsor/product-component
```

Регистрация маппинга через атрибуты Doctrine — отдельных XML/YAML не требуется.

