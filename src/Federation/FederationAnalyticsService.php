<?php

declare(strict_types=1);

namespace App\Component\Product\Federation;

use Doctrine\DBAL\Connection;

final class FederationAnalyticsService
{
    public function __construct(private Connection $db, private TenantFederationPolicy $policy)
    {
    }

    public function metrics(string $viewer): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM federated_metrics');

        return array_values(array_filter($rows, fn ($r) => $this->policy->canAccess($viewer, $r['tenant_id'])));
    }
}
