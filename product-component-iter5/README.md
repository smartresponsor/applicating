# Product Component v5 (Catalog Layer)

Добавлено: варианты (SKU-variant), опции/значения, атрибуты, категории (дерево), быстрый CatalogQuery API (фильтры).
Следует контракту доменной изоляции и snake_case колонкам.

## Состав
- Entities: ProductVariant, ProductOption, ProductOptionValue, ProductAttribute, ProductCategory
- DTO: ProductVariantCreateDTO, ProductAssignAttributesDTO
- Service: ProductVariantService, ProductCatalogService, ProductCategoryService
- Catalog API (доменный сервис): фильтры по price/status/stock/category/attributes
- Миграции: 003_catalog.sql
- Тесты: VariantsAndCatalogTest
