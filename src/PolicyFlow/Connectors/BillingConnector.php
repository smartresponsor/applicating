<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow\Connectors;

use App\Component\Product\PolicyFlow\PolicyConnectorInterface;
use Doctrine\DBAL\Connection;

final class BillingConnector implements PolicyConnectorInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function execute(array $context): array
    {
        $tenant = (string) ($context['tenant']['id'] ?? 'tenantA');
        $discount = (float) ($context['effects']['discount'] ?? 0.0);
        $this->db->insert('policy_flow_log', [
            'ts' => gmdate('c'), 'policy' => $context['policy'] ?? 'unknown',
            'step' => 'billing.apply', 'status' => 'ok', 'payload' => json_encode(['discount' => $discount]),
        ]);

        return ['ok' => true, 'applied_discount' => $discount, 'tenant' => $tenant];
    }
}
