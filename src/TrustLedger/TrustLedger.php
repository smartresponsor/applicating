<?php

declare(strict_types=1);

namespace App\Component\Product\TrustLedger;

use Doctrine\DBAL\Connection;

final class TrustLedger
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function head(): ?array
    {
        return $this->db->fetchAssociative('SELECT * FROM trust_ledger ORDER BY id DESC LIMIT 1');
    }

    public function append(string $payload, string $signature): array
    {
        $prev = $this->head();
        $prevHash = $prev['hash'] ?? 'GENESIS';
        $hash = LedgerHasher::hash($prevHash, $payload, $signature);
        $this->db->insert('trust_ledger', [
            'ts' => gmdate('c'),
            'prev_hash' => $prevHash,
            'payload' => $payload,
            'signature' => $signature,
            'hash' => $hash,
        ]);

        return ['ok' => true, 'hash' => $hash];
    }

    public function verifyChain(int $depth = 1000): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM trust_ledger ORDER BY id DESC LIMIT ?', [$depth]);
        if (!$rows) {
            return ['ok' => true, 'verified' => true, 'depth' => 0];
        }
        $prevHash = 'GENESIS';
        foreach (array_reverse($rows) as $r) {
            $expected = LedgerHasher::hash($prevHash, (string) $r['payload'], (string) $r['signature']);
            if (!hash_equals($expected, (string) $r['hash'])) {
                return ['ok' => false, 'verified' => false, 'bad_id' => $r['id']];
            }
            $prevHash = (string) $r['hash'];
        }

        return ['ok' => true, 'verified' => true, 'depth' => count($rows)];
    }
}
