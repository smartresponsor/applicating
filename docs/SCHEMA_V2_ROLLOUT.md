# Product Schema v2 — Zero‑Downtime Rollout

## Цель
Перейти на `product_title_v2`, `product_slug`, `product_brand`, `product_meta` без простоя.

## План
1. **Миграция (additive)** — применить `006_product_v2.sql`.
2. **Dual writes** — триггер `trg_product_dualwrite_v2` синхронизирует `product_read`.
3. **Backfill** — выполнить `php scripts/backfill_product_v2.php` до стабильно нулевой дельты.
4. **Canary** — включить флаг `FF_PRODUCT_V2_READ=on` на 10% трафика (через конфиг/переменные окружения).
5. **Наблюдение** — сравнить p95/p99 latency, 5xx, карточки товара в read/API.
6. **Cutover** — переключить все чтения на v2 поля (API слой использует `product_read_v2`).
7. **Cleanup** — через 2 недели удалить legacy-путь и (по желанию) старые поля после freeze-периода.

## Feature Flags
- `FF_PRODUCT_V2_WRITE` — писать в v2 поля (`product_title_v2`, `product_slug`, `product_brand`, `product_meta`).
- `FF_PRODUCT_V2_READ` — читать поля из `product_read`/`product_read_v2` (использует v2, если включено).

## Мониторинг
- Следить за: `job:http_5xx_rate:ratio`, `job:http_latency_p99_seconds` (см. итерацию XV).
- Добавить сравнение `canary vs stable` (итерация XIV).

## Откат
- Отключить `FF_PRODUCT_V2_READ`, вернуть чтение на legacy поля.
- Dual writes оставляем включёнными до выяснения — read и write останутся консистентными.
