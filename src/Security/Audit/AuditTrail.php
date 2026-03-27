<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Audit;

use Doctrine\DBAL\Connection;

final class AuditTrail
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $meta */
    public function log(string $tenantId, string $actor, string $action, array $meta = []): void
    {
        $this->db->insert('audit_logs', [
            'ts' => gmdate('c'),
            'tenant_id' => $tenantId,
            'actor' => $actor,
            'action' => $action,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
