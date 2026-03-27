<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class PolicyRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $rules */
    public function save(string $scope, string $name, array $rules): int
    {
        $this->db->insert('gov_policies', [
            'scope' => $scope, 'name' => $name, 'rules' => json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'version' => 1, 'created_at' => gmdate('c'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<int,array<string,mixed>> */
    public function list(string $scope): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM gov_policies WHERE scope=? ORDER BY created_at DESC', [$scope]);
    }
}
