# Trust Ledger (v30.1)
Обновлено: 2025-10-09

## Назначение
Неподделываемый журнал изменений политик (репликаций, rollout'ов, learning-апдейтов). Каждая запись включает:
- `prev_hash` — ссылка на предыдущий блок;
- `payload` — JSON-событие (например, { "type":"replicate", "policy":"...", ... });
- `signature` — HMAC подпись payload;
- `hash` — SHA-256(prev_hash|payload|signature).

## API
- `GET  /api/trust-ledger/head` — текущий хеш (голова цепочки).
- `POST /api/trust-ledger/append` — добавить событие (требует корректной подписи).
- `GET  /api/trust-ledger/verify?depth=N` — сверка цепочки последних N блоков.

## Интеграция с Federation/Scheduler
- После успешного push или apply — аппенд события в Ledger.
- В Scheduler — записывать canary шаги и результаты (ok/fail).
- В Self-Learning — записывать глобальные обновления alpha/beta.
