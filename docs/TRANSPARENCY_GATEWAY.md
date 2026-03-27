# Transparency Gateway (v39.0)
Обновлено: 2025-10-09

## Публичные эндпоинты (read-only)
- GET `/api/public/metrics?window=60` — агрегированные метрики
- GET `/api/public/audit?limit=50` — срез immutable-цепочки
- GET `/api/public/export?from=...&to=...` — выгрузка событий из audit log

## Безопасность
- Rate-limit (Ingress), read-only, без персональных данных.
- Рекомендуется отдельный домен и сеть (DMZ).

## Деплой
1) Применить `064_transparency_gateway.sql` (опционально).
2) Включить Ingress `smartpolicy-transparency-public`.
3) Подключить Dashboard (static host).
