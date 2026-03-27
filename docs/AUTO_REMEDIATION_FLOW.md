# Auto-Remediation (v32.0)
Обновлено: 2025-10-09

## Поток
1) Intelligence Hub → `/insights` (окно 30–60 мин).
2) Auto-Remediation CronJob вызывает `/remediation/run` c aggregate+insights.
3) Выполняются действия: STOP_ROLLOUT / RESUME_ROLLOUT / SCALE_SCHEDULER / CHECK_FEDERATION / VERIFY_LEDGER.
4) Лог в `policy_remediation_log`.

## Безопасность
- Ограничить доступ к `/remediation/run` (mtls/ip allowlist).
- Все действия желательно писать в Trust Ledger.

## Интеграция с Scheduler
- Overriding canary percent через параметр `overridePercent` у `/scheduler/tick`.
