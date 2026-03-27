# End‑to‑End Autopilot (v21.3)
*updated 2025-10-08*

## Что делает
Nightly‑пайплайн прогоняет: метрики → прогноз → политика → действие → аудит → UI.

## Быстрый старт
```bash
# Пробный запуск (использует SQLite mock)
bin/autopilot-run tenantA 40,48,55,63,71
# файл autopilot.sqlite появится рядом с bin/ (таблица autopilot_events)
```

## Интеграция с существующими модулями
- Forecast: берём из v21.1 (moving average + slope).
- Action: маппим решение в v21.2 Orchestrator (в демо — помечено как simulated).
- UI: страница admin-ui/Autopilot.tsx отображает последние события.

## Конфигурация
- `config/autopilot.yaml` — пороги и приоритеты.
- cron — см. workflow.
