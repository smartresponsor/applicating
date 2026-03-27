# Developer Hub & SDK (v24.3)
*обновлено 2025-10-09*

## Быстрый старт
```bash
bin/sr-dev init my-plugin
bin/sr-dev publish my-plugin
```

## REST
- `POST /api/devhub/keys/issue` — выдать API-ключ
- `POST /api/devhub/keys/revoke` — отозвать ключ
- `GET  /api/devhub/plugin/template` — сгенерировать `plugin.json`
- `POST /api/devhub/plugin/publish` — публикация (через Publisher)
- `POST /api/devhub/subgraph/test` — валидация SDL

## Интеграция
- API-ключи пригодятся для Federated GraphQL и Marketplace.
- Publisher пишет события в `devhub_audit`.
