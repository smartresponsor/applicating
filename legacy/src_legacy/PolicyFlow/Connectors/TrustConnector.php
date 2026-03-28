<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow\Connectors;

use App\Component\Product\PolicyFlow\PolicyConnectorInterface;
use Doctrine\DBAL\Connection;

final class TrustConnector implements PolicyConnectorInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function execute(array $context): array
    {
        $tenant = (string) ($context['tenant']['id'] ?? 'tenantA');
        $score = (float) ($this->db->fetchOne('SELECT score FROM trustmesh_scores WHERE tenant_id=? ORDER BY ts DESC LIMIT 1', [$tenant]) ?? 0.0);

        return ['ok' => true, 'trustscore' => $score];
    }
}
