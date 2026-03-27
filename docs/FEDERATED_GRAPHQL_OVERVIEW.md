# Federated GraphQL Gateway (v24.0)
*обновлено 2025-10-09*

## Что делает
- Регистрирует subgraphs в `gql_subgraphs`.
- Собирает федеративную схему (простая конкатенация для демо).
- Ограничивает стоимость запросов (QueryCostLimiter).

## REST
- `GET /api/graphql/schema` — получить SDL шлюза + сабграфов.
- `POST /api/graphql` — выполнить запрос (демо-обработчик health/abiForecast/orchestrationEmit).

## CLI
```bash
bin/graphql-subgraph-register abi http://abi:8080/graphql "$(cat abi.graphqls)"
bin/graphql-build > combined.graphqls
```

## Дальше
- Внедрить настоящий Apollo Federation (subgraph composition).
- AuthN/AuthZ на основе v23.5 Security.
- Лимиты по арендаторам и биллингу (v24.1).
