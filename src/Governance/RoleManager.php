<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class RoleManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function assign(string $tenantId, string $userId, string $role): void
    {
        $this->db->insert('gov_roles', [
            'tenant_id' => $tenantId, 'user_id' => $userId, 'role' => $role, 'assigned_at' => gmdate('c'),
        ]);
    }

    /** @return array<int,string> */
    public function rolesOf(string $tenantId, string $userId): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT role FROM gov_roles WHERE tenant_id=? AND user_id=?', [$tenantId, $userId]);

        return array_map(fn ($r) => $r['role'], $rows);
    }
}
