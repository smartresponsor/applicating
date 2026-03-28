<?php

declare(strict_types=1);

namespace App\Component\Product\SystemAudit;

use Doctrine\DBAL\Connection;

/** Пишет события в append-only журнал и хранит цепочку хэшей. */
final class ImmutableLedgerWriter
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function append(string $type, array $data): array
    {
        $prev = $this->db->fetchOne('SELECT hash FROM system_audit_ledger ORDER BY id DESC LIMIT 1');
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $hash = hash('sha256', (string) $prev.'|'.$type.'|'.$payload);
        $this->db->insert('system_audit_ledger', [
            'ts' => gmdate('c'),
            'type' => $type,
            'payload' => $payload,
            'prev_hash' => (string) $prev,
            'hash' => $hash,
        ]);

        return ['ok' => true, 'hash' => $hash, 'prev' => $prev];
    }

    public function verify(int $depth = 1000): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT id, type, payload, prev_hash, hash FROM system_audit_ledger ORDER BY id DESC LIMIT ?', [$depth]);
        $ok = true;
        $brokenAt = null;
        $prev = null;
        foreach (array_reverse($rows) as $r) {
            $calc = hash('sha256', (string) $prev.'|'.(string) $r['type'].'|'.(string) $r['payload']);
            if ($calc !== (string) $r['hash']) {
                $ok = false;
                $brokenAt = (int) $r['id'];
                break;
            }
            $prev = (string) $r['hash'];
        }

        return ['ok' => $ok, 'broken_at' => $brokenAt];
    }
}
