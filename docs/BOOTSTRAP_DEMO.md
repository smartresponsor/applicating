# Bootstrap Demo Environment

## Быстрый старт
```bash
curl -fsSL https://raw.githubusercontent.com/smartresponsor/install/main/bootstrap.sh | bash
```
или локально:
```bash
make demo-up
```

## Что делает скрипт
1. Проверяет зависимости (Docker, PHP, Make).
2. Поднимает Postgres+Redis (из `docker/docker-compose.yml` пакета).
3. Применяет миграции (`001..007`).
4. Запускает seed (`scripts/demo_seed.php` или `demo_seed.sql`).
5. Стартует API на `http://localhost:8080/api/catalog`.

## Команды Make
- `make demo-up` — полное развертывание с демо.
- `make demo-reset` — сброс и перезапуск.
- `make demo-seed` — только наполнение тестовыми данными.

## Пример данных
Товары `Demo Product #1..#10`, активные, по 100 ед. на складе, с брендами Acme/Globex/Umbrella/Soylent.

## Дальше
- Проверь эндпоинт `/api/catalog`.
- Смотри OpenAPI (`openapi/catalog.yaml`).
- Можешь обновить seed или миграции для кастомных сценариев QA.
