<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Stripe;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class StripeWebhookController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function __invoke(Request $req): JsonResponse
    {
        $event = json_decode($req->getContent() ?: '{}', true);
        $type = $event['type'] ?? 'unknown';
        // Minimal demo mapping
        if ('invoice.payment_failed' === $type) {
            $subId = $event['data']['object']['subscription'] ?? '';
            $this->db->executeStatement('UPDATE subscriptions SET status = :st WHERE stripe_subscription_id = :sid', ['st' => 'past_due', 'sid' => $subId]);
        }
        if ('customer.subscription.deleted' === $type) {
            $subId = $event['data']['object']['id'] ?? '';
            $this->db->executeStatement('UPDATE subscriptions SET status = :st WHERE stripe_subscription_id = :sid', ['st' => 'canceled', 'sid' => $subId]);
        }

        return new JsonResponse(['ok' => true, 'type' => $type]);
    }
}
