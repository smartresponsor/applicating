<?php

declare(strict_types=1);

namespace App\Component\Product\Policy;

use Doctrine\DBAL\Connection;

final class PolicyRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function register(string $name, string $content, string $fmt = 'yaml'): int
    {
        $this->db->insert('policy_registry', [
            'ts' => gmdate('c'), 'name' => $name, 'format' => $fmt, 'content' => $content, 'version' => 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function latest(string $name): ?array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM policy_registry WHERE name=? ORDER BY ts DESC LIMIT 1', [$name]);

        return $row ?: null;
    }

    public function audit(string $name, string $subject, string $decision, array $ctx = []): void
    {
        $this->db->insert('policy_audit', [
            'ts' => gmdate('c'), 'name' => $name, 'subject' => $subject, 'decision' => $decision,
            'context' => json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
