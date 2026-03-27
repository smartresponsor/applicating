<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

use Doctrine\DBAL\Connection;

final class ApiKeyRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByKey(string $tenantId, string $apiKey): ?array
    {
        $hash = hash('sha256', $apiKey);
        $row = $this->db->fetchAssociative(
            'SELECT * FROM api_keys WHERE tenant_id = :t AND key_hash = :h AND active = TRUE AND (active_until IS NULL OR active_until > NOW())',
            ['t' => $tenantId, 'h' => $hash]
        );
        if (!$row) {
            return null;
        }
        $row['scopes'] = is_array($row['scopes']) ? $row['scopes'] : [];
        $row['roles'] = is_array($row['roles']) ? $row['roles'] : ['VIEWER'];

        return $row;
    }

    public function create(string $tenantId, string $name, string $apiKeyPlain, array $scopes = [], array $roles = ['VIEWER'], int $rateLimit = 120, ?\DateTimeImmutable $activeUntil = null): void
    {
        $this->db->insert('api_keys', [
            'tenant_id' => $tenantId,
            'name' => $name,
            'key_hash' => hash('sha256', $apiKeyPlain),
            'scopes' => '{'.implode(',', array_map(fn ($s) => '"'.addslashes($s).'"', $scopes)).'}',
            'roles' => '{'.implode(',', array_map(fn ($s) => '"'.addslashes($s).'"', $roles)).'}',
            'rate_limit_per_minute' => $rateLimit,
            'active_until' => $activeUntil?->format('Y-m-d H:i:s'),
            'active' => true,
        ]);
    }
}
