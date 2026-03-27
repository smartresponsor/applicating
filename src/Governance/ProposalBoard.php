<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class ProposalBoard
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function propose(string $tenantId, string $title, string $body, string $author): int
    {
        $this->db->insert('gov_proposals', [
            'tenant_id' => $tenantId, 'title' => $title, 'body' => $body, 'author' => $author, 'status' => 'open', 'created_at' => gmdate('c'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function vote(int $proposalId, string $userId, string $choice): void
    {
        $this->db->insert('gov_votes', [
            'proposal_id' => $proposalId, 'user_id' => $userId, 'choice' => $choice, 'ts' => gmdate('c'),
        ]);
    }

    public function tally(int $proposalId): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT choice, COUNT(*) c FROM gov_votes WHERE proposal_id=? GROUP BY choice', [$proposalId]);
        $out = [];
        foreach ($rows as $r) {
            $out[$r['choice']] = (int) $r['c'];
        }

        return $out;
    }

    public function close(int $proposalId, string $result): void
    {
        $this->db->update('gov_proposals', ['status' => 'closed', 'result' => $result, 'closed_at' => gmdate('c')], ['id' => $proposalId]);
    }
}
