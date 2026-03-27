# Billing & Usage Engine (v24.1)
*обновлено 2025-10-09*

## Простыми словами
- У каждого арендатора есть **баланс** в USD.
- Любое действие в системе списывает **стоимость** по прайсу.
- Пополнять можно через **Stripe**, **PayPal** или **Crypto (USDT/USDC)**.
- Есть **лимиты** на месяц и **история операций**.

## Таблицы
- `billing_wallets` — баланс арендатора
- `billing_prices` — прайс по фичам
- `billing_usage` — учёт использования
- `billing_invoices` — счета
- `billing_payments` — платежи
- `billing_limits` — месячные лимиты
- `billing_events` — события кошелька

## API (демо)
- `POST /api/billing/track` — учесть использование
- `GET  /api/billing/balance` — баланс
- `POST /api/billing/topup/stripe|paypal|crypto` — пополнение
- `POST /api/billing/invoice` — создать счёт

## CLI
```bash
bin/bill-topup tenantA 25 stripe
bin/bill-usage tenantA llm.call 3
bin/bill-limit tenantA 100
bin/bill-invoice tenantA 2025-10
bin/bill-report tenantA 2025-10
```

## Интеграция
- Включить `UsageTracker` в Federated GraphQL Gateway (middleware).
- Перед тяжёлыми операциями вызывать `LimitEnforcer->allow()`.
- После оплаты — `WalletService->topup()`.
