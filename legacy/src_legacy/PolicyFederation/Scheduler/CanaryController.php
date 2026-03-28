<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation\Scheduler;

use Doctrine\DBAL\Connection;

final class CanaryController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function getPercent(string $policy, int $version): int
    {
        $p = $this->db->fetchOne('SELECT percent FROM policy_canary WHERE name=? AND version=?', [$policy, $version]);

        return (int) ($p ?? 20);
    }

    public function setPercent(string $policy, int $version, int $percent): void
    {
        $exists = (int) $this->db->fetchOne('SELECT COUNT(*) FROM policy_canary WHERE name=? AND version=?', [$policy, $version]);
        if ($exists) {
            $this->db->update('policy_canary', ['percent' => $percent, 'ts' => gmdate('c')], ['name' => $policy, 'version' => $version]);
        } else {
            $this->db->insert('policy_canary', ['name' => $policy, 'version' => $version, 'percent' => $percent, 'ts' => gmdate('c')]);
        }
    }
}
