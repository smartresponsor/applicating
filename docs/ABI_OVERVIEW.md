# Adaptive Business Intelligence (v22.0)
*обновлено 2025-10-09*

## Цель
Перенести принципы Autopilot на бизнес‑уровень: прогноз выручки, адаптивные цены, SLA, кредиты/промо.

## Компоненты
- `Forecast/RevenueForecast` — среднее + тренд, простой прогноз.
- `Policy/BusinessPolicyEngine` — правила решений (price_adjust_up, sla_raise, promo_credit).
- `Action/BusinessActionOrchestrator` — применение в БД (pricing, sla_plans, credits).
- `ABIRunner` — полный цикл расчёта и запись в `abi_audit_events`.

## CLI
```bash
bin/abi-run tenantA 1200,1300,1500,1700,2000 0.35 gold
```

## Хранилище
Таблица `abi_audit_events` с полями: ts, tenant_id, revenue_forecast, action, outcome, details.
