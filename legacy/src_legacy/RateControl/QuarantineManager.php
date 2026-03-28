<?php

declare(strict_types=1);

namespace App\Component\Product\RateControl;

use Doctrine\DBAL\Connection;

final class QuarantineManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function isQuarantinedIp(string $ip): bool
    {
        return (bool) $this->db->fetchOne("SELECT 1 FROM quarantine WHERE kind='ip' AND value=? AND (until IS NULL OR until>NOW())", [$ip]);
    }

    public function isQuarantinedTenant(string $tenant): bool
    {
        return (bool) $this->db->fetchOne("SELECT 1 FROM quarantine WHERE kind='tenant' AND value=? AND (until IS NULL OR until>NOW())", [$tenant]);
    }

    public function put(string $kind, string $value, int $seconds = 600, string $reason = 'attack'): void
    {
        $this->db->insert('quarantine', [
            'kind' => $kind, 'value' => $value, 'reason' => $reason, 'since' => gmdate('c'), 'until' => gmdate('c', time() + $seconds),
        ]);
    }
}
