# Anti‑DDoS & Abuse Guard (v27.4)
*обновлено 2025-10-09*

## Что делает
- Блокирует трафик по бан‑листам IP/tenant.
- Детектирует bursts по событийному количеству за 60с → временный бан IP.
- Поддерживает капчу‑вызов для разбанивания (демо).

## Подключение
1. Включите `AbuseGuardMiddleware` в цепочку Gateway.
2. Настройте правила в `config/abuseguard.rules.yaml`.
3. Храните события/баны в таблицах `abuse_*`.

## API
- `POST /api/guard/captcha/verify {tenant, token}` → `ok|captcha_failed`.

## Совместимость
- Работает с Priority Queues (v27.3) и TrustMesh (v27.2): можно комбинировать штрафы/приоритеты.
