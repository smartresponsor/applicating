<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Consensus;

use Doctrine\DBAL\Connection;

final class ConsensusEngine
{
    public function __construct(private readonly Connection $db, private readonly LwwMap $crdt)
    {
    }

    public function propose(string $topic, array $payload, string $region): int
    {
        $this->db->insert('policy_mesh_proposals', [
            'ts' => gmdate('c'),
            'topic' => $topic,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'region' => $region,
            'status' => 'proposed',
        ]);
        /** @var int */
        $id = (int) $this->db->lastInsertId();
        // автор автоматом голосует "approve"
        $this->vote($id, $region, 'approve');

        return $id;
    }

    public function vote(int $proposalId, string $region, string $vote): void
    {
        $this->db->insert('policy_mesh_votes', [
            'ts' => gmdate('c'),
            'proposal_id' => $proposalId,
            'region' => $region,
            'vote' => $vote,
        ]);
    }

    public function tryFinalize(int $proposalId, int $quorum): array
    {
        $p = $this->db->fetchAssociative('SELECT * FROM policy_mesh_proposals WHERE id=?', [$proposalId]);
        if (!$p) {
            return ['ok' => false, 'error' => 'proposal_not_found'];
        }

        $rows = $this->db->fetchAllAssociative('SELECT vote FROM policy_mesh_votes WHERE proposal_id=?', [$proposalId]);
        $yes = 0;
        $no = 0;
        foreach ($rows as $r) {
            if (($r['vote'] ?? '') === 'approve') {
                ++$yes;
            } else {
                $no++;
            }
        }
        $total = $yes + $no;
        if ($total < $quorum) {
            return ['ok' => false, 'status' => 'pending', 'yes' => $yes, 'no' => $no, 'needed' => $quorum];
        }

        if ($yes > $no) {
            $payload = json_decode((string) $p['payload'], true) ?: [];
            $ts = time();
            foreach ($payload as $k => $v) {
                $this->crdt->put((string) $k, $v, $ts);
            }
            $this->db->update('policy_mesh_proposals', ['status' => 'applied', 'applied_at' => gmdate('c')], ['id' => $proposalId]);

            return ['ok' => true, 'status' => 'applied', 'map' => $this->crdt->export(), 'yes' => $yes, 'no' => $no];
        }
        $this->db->update('policy_mesh_proposals', ['status' => 'rejected', 'applied_at' => gmdate('c')], ['id' => $proposalId]);

        return ['ok' => true, 'status' => 'rejected', 'yes' => $yes, 'no' => $no];
    }
}
