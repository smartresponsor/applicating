# Plugin Marketplace (v24.2)
*обновлено 2025-10-09*

## Что есть
- Публикация плагинов (manifest + signature).
- Каталог, установка/удаление для арендатора.
- Песочница (демо): запись событий запуска.
- Интеграция с Billing: подписка при установке платного плагина.

## Таблицы
`marketplace_plugins`, `marketplace_releases`, `tenant_plugins`, `plugin_events`.

## API
- POST /api/marketplace/publish (manifest, signature)
- GET  /api/marketplace/list
- POST /api/marketplace/install (tenant, plugin_id, version)
- POST /api/marketplace/run (tenant, plugin_id, entry, args)

## CLI
```bash
bin/plugin-publish '{{"name":"Smart Forecast Pro","version":"1.1.0","author":"DataForge","price_usd":5.0,"permissions":["read:forecast"]}}' demo
bin/plugin-list
bin/plugin-install tenantA 1 1.1.0
bin/plugin-uninstall tenantA 1
```

## Дальше
- Реальный sandbox (Docker/Firecracker), лимиты CPU/RAM/Network.
- Подписи: Ed25519, публичный каталог ключей.
- Совместимость версий ядра и требований плагина.
