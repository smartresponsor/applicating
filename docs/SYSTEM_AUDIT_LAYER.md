# System Integrity & Audit Layer (v38.0)
Обновлено: 2025-10-09

## Назначение
Гарантировать прозрачность и доказуемость решений Smartresponsor. Все события пишутся в audit log и immutable ledger.

## API
- POST `/api/audit/log` — журнал событий
- POST `/api/audit/append` — запись в append-only цепочку
- GET  `/api/audit/check?depth=1000` — верификация цепочки

## Интеграция
- Meta-Coordination: логировать pipeline run (train/adjust/evaluate/enforce/remediation).
- Governance/Orchestrator: писать ключевые решения в immutable ledger.
