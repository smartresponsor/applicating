<?php

declare(strict_types=1);

namespace App\Component\Product\Audit;

use Doctrine\DBAL\Connection;

final class AuditLogger
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed>|null $payload */
    public function log(string $actor, string $method, string $path, int $status, ?string $ip = null, ?string $ua = null, ?array $payload = null): void
    {
        $this->db->insert('audit_log', [
            'actor' => $actor,
            'ip' => $ip,
            'method' => strtoupper($method),
            'path' => $path,
            'status' => $status,
            'user_agent' => $ua,
            'payload' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    }
}
