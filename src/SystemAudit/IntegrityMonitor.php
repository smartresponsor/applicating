<?php

declare(strict_types=1);

namespace App\Component\Product\SystemAudit;

use Doctrine\DBAL\Connection;

/** Слушает события из Mesh/Governance/Sentient и пишет контрольные записи. */
final class IntegrityMonitor
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function log(string $source, string $event, array $payload = []): void
    {
        $this->db->insert('system_audit_log', [
            'ts' => gmdate('c'),
            'source' => $source,
            'event' => $event,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'hash' => hash('sha256', $source.'|'.$event.'|'.json_encode($payload)),
        ]);
    }
}
