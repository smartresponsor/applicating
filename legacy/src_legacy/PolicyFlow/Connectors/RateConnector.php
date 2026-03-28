<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow\Connectors;

use App\Component\Product\PolicyFlow\PolicyConnectorInterface;
use Doctrine\DBAL\Connection;

final class RateConnector implements PolicyConnectorInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function execute(array $context): array
    {
        $tenant = (string) ($context['tenant']['id'] ?? 'tenantA');
        $plan = (string) ($this->db->fetchOne('SELECT plan FROM smartcloud_tenants WHERE tenant_id=?', [$tenant]) ?? 'standard');

        return ['ok' => true, 'plan' => $plan];
    }
}
