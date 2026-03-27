# Policy Federation (v29.0)
*обновлено 2025-10-09*

## Идея
- Мульти-региональная репликация политик (подпись, очередь, пулы пиров).

## API
- GET/POST `/api/policy/federation/peers`
- POST `/api/policy/federation/replicate` {name,version}
- POST `/api/policy/federation/push` {payload,signature}

## Поток
1. Новая версия → в очередь `policy_replica_queue`.
2. Планировщик отправляет payload (подписан FederationSigner) пирами.
3. Принимающая сторона проверяет подпись и пишет в `policy_registry`.
