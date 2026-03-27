# Billing & Stripe Integration (v20.2)
*updated 2025-10-08*

## Схема данных
- `billing_plans` — планы и лимиты (rate_limit_per_minute, monthly_quota_requests).
- `subscriptions` — привязка арендатора к плану, периоды.
- `usage_events` — запись потребления (API вызовы и др.).
- `invoices` — рассчитанные счета.

## Поток
1) При запросах — записываем `usage_events` и проверяем квоту/лимиты.
2) Раз в месяц — `InvoicingService::computeMonthly()` → `persistInvoice()`.
3) Stripe: создаём/обновляем customer/subscription, публикуем Invoice Items при перерасходе.

## Примеры
```php
$plans = new App\Component\Product\Billing\Core\BillingPlanService($db);
$plans->create('pro', ['name'=>'Pro','price'=>4900,'rate'=>300,'quota'=>50000]);

$subs = new App\Component\Product\Billing\Core\SubscriptionService($db);
$subs->subscribe('tenantA','pro');

$usage = new App\Component\Product\Billing\Core\UsageRecorder($db);
$usage->add('tenantA','api_request',1,['route'=>'/api/catalog']);
$check = $usage->checkQuota('tenantA', 50000);
```

## Webhooks
- `StripeWebhookController` обрабатывает `invoice.payment_failed` и `customer.subscription.deleted`.

## Makefile (рекомендуется)
- `make invoice-run` — вычислить и сохранить счета за прошлый месяц.
- `make usage-dump` — выгрузить usage по всем арендаторам.

## Безопасность
- Ключи Stripe через ENV (`STRIPE_API_KEY`, `STRIPE_WEBHOOK_SECRET`).
- Rate-limit и квоты должны быть связаны с v18.4 Tenant Quotas.
