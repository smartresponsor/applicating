# ABI/CLA Dashboard (v22.4)
*обновлено 2025-10-09*

## Что делает
- Агрегирует KPI и серии для графиков из ABI, Pricing, SLA, CLA.
- Отдаёт JSON через `/api/dashboard/kpi` и `/api/dashboard/charts`.
- UI-страница `admin-ui/src/pages/Dashboard.tsx` визуализирует блоки.

## KPI
- total_revenue_predicted, avg_price_delta_pct, sla_breaches, churn_avg_score, winback_success_rate.

## CLI
```bash
bin/dashboard-refresh
```
