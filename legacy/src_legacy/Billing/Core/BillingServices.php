<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Core;

use Doctrine\DBAL\Connection;

final class BillingPlanService
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<string,mixed>|null */
    public function getPlan(string $id): ?array
    {
        return $this->db->fetchAssociative('SELECT * FROM billing_plans WHERE id=:id AND active=TRUE', ['id' => $id]) ?: null;
    }

    /** @param array{name:string,price:int,currency?:string,rate:int,quota:int,features?:array} $data */
    public function create(string $id, array $data): void
    {
        $this->db->insert('billing_plans', [
            'id' => $id,
            'name' => $data['name'],
            'monthly_price_cents' => $data['price'],
            'currency' => $data['currency'] ?? 'USD',
            'rate_limit_per_minute' => $data['rate'],
            'monthly_quota_requests' => $data['quota'],
            'features' => json_encode($data['features'] ?? []),
        ]);
    }
}

final class SubscriptionService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function subscribe(string $tenantId, string $planId): void
    {
        $this->db->insert('subscriptions', [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'current_period_start' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
            'current_period_end' => (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s'),
        ]);
    }

    /** @return array<string,mixed>|null */
    public function current(string $tenantId): ?array
    {
        return $this->db->fetchAssociative('SELECT * FROM subscriptions WHERE tenant_id=:t ORDER BY id DESC LIMIT 1', ['t' => $tenantId]) ?: null;
    }
}

final class UsageRecorder
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function add(string $tenantId, string $kind = 'api_request', int $qty = 1, array $meta = []): void
    {
        $this->db->insert('usage_events', [
            'tenant_id' => $tenantId,
            'kind' => $kind,
            'quantity' => $qty,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /** @return array{requests:int,quota:int,within:bool} */
    public function checkQuota(string $tenantId, int $monthlyQuota): array
    {
        $start = (new \DateTimeImmutable('first day of this month'))->format('Y-m-d 00:00:00');
        $end = (new \DateTimeImmutable('last day of this month 23:59:59'))->format('Y-m-d H:i:s');
        $used = (int) $this->db->fetchOne(
            'SELECT COALESCE(SUM(quantity),0) FROM usage_events WHERE tenant_id=:t AND occurred_at BETWEEN :s AND :e',
            ['t' => $tenantId, 's' => $start, 'e' => $end]
        );

        return ['requests' => $used, 'quota' => $monthlyQuota, 'within' => $used < $monthlyQuota];
    }
}

final class InvoicingService
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array{subtotal:int,tax:int,total:int} */
    public function computeMonthly(string $tenantId, int $basePriceCents, int $includedQuota, int $overagePricePer1kCents): array
    {
        $start = (new \DateTimeImmutable('first day of last month'))->format('Y-m-d 00:00:00');
        $end = (new \DateTimeImmutable('last day of last month 23:59:59'))->format('Y-m-d H:i:s');
        $used = (int) $this->db->fetchOne(
            'SELECT COALESCE(SUM(quantity),0) FROM usage_events WHERE tenant_id=:t AND occurred_at BETWEEN :s AND :e',
            ['t' => $tenantId, 's' => $start, 'e' => $end]
        );
        $over = max(0, $used - $includedQuota);
        $blocks = (int) ceil($over / 1000);
        $overage = $blocks * $overagePricePer1kCents;
        $subtotal = $basePriceCents + $overage;
        $tax = (int) round($subtotal * 0.2); // 20% VAT example
        $total = $subtotal + $tax;

        return ['subtotal' => $subtotal, 'tax' => $tax, 'total' => $total];
    }

    public function persistInvoice(string $tenantId, array $calc): void
    {
        $ps = (new \DateTimeImmutable('first day of last month'))->format('Y-m-d 00:00:00');
        $pe = (new \DateTimeImmutable('last day of last month 23:59:59'))->format('Y-m-d H:i:s');
        $this->db->insert('invoices', [
            'tenant_id' => $tenantId,
            'period_start' => $ps,
            'period_end' => $pe,
            'subtotal_cents' => $calc['subtotal'],
            'tax_cents' => $calc['tax'],
            'total_cents' => $calc['total'],
            'currency' => 'USD',
        ]);
    }
}
