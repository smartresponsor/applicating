# Global Rate Control & Quarantine (v27.5)
*обновлено 2025-10-09*

## Режимы
- **normal** — базовые лимиты по планам
- **soft** — снижение лимитов до 70%
- **low_power** — 30% лимитов, только критичный трафик

## Компоненты
- `GlobalRateController` — определяет режим по CPU/нагрузке.
- `RateGuardMiddleware` — дросселирование per-tenant.
- `QuarantineManager` — карантин IP/tenant.
- `EnvoyRateSync` — экспорт лимитов в Envoy/Redis.

## API
- `GET /api/rate/mode`
- `POST /api/rate/quarantine/put {{kind,value,seconds}}`
- `POST /api/rate/envoy/export`

## Интеграция
- Работает совместно с Anti‑DDoS (v27.4) и Priority Queues (v27.3).
