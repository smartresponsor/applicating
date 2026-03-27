<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Stripe;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AdminStripeController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function show(Request $r, string $tenantId): JsonResponse
    {
        $row = $this->db->fetchAssociative('SELECT stripe_customer_id, stripe_subscription_id, status FROM subscriptions WHERE tenant_id=:t ORDER BY id DESC LIMIT 1', ['t' => $tenantId]) ?: null;
        if (!$row) {
            return new JsonResponse(['tenant_id' => $tenantId, 'status' => 'no_subscription']);
        }

        return new JsonResponse(['tenant_id' => $tenantId, 'customer_id' => $row['stripe_customer_id'], 'subscription_id' => $row['stripe_subscription_id'], 'status' => $row['status']]);
    }
}
