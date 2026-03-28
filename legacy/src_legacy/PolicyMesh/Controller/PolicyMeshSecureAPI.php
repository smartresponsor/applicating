<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Controller;

use App\Component\Product\PolicyMesh\Consensus\ConsensusEngine;
use App\Component\Product\PolicyMesh\Consensus\LwwMap;
use App\Component\Product\PolicyMesh\Security\Rbac;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PolicyMeshSecureAPI
{
    public function __construct(
        private readonly ConsensusEngine $engine,
        private readonly LwwMap $map,
        private readonly Rbac $rbac,
        private readonly Connection $db,
    ) {
    }

    private function role(Request $r): string
    {
        return (string) ($r->headers->get('X-Role') ?? 'observer');
    }

    private function requirePerm(Request $r, string $perm): ?JsonResponse
    {
        $role = $this->role($r);
        if (!$this->rbac->allow($role, $perm)) {
            return new JsonResponse(['ok' => false, 'error' => 'forbidden', 'role' => $role, 'need' => $perm], 403);
        }

        return null;
    }

    #[Route('/api/policy/mesh/secure/propose', methods: ['POST'])]
    public function propose(Request $req): JsonResponse
    {
        if ($res = $this->requirePerm($req, 'mesh.propose')) {
            return $res;
        }
        $topic = (string) $req->get('topic', 'policy.params');
        $payload = json_decode((string) $req->get('payload', '{}'), true) ?: [];
        $region = (string) $req->get('region', 'local');
        $id = $this->engine->propose($topic, $payload, $region);

        return new JsonResponse(['ok' => true, 'proposal_id' => $id]);
    }

    #[Route('/api/policy/mesh/secure/vote', methods: ['POST'])]
    public function vote(Request $req): JsonResponse
    {
        if ($res = $this->requirePerm($req, 'mesh.vote')) {
            return $res;
        }
        $id = (int) $req->get('proposal_id', 0);
        $region = (string) $req->get('region', 'local');
        $vote = (string) $req->get('vote', 'approve');
        $this->engine->vote($id, $region, $vote);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/policy/mesh/secure/finalize', methods: ['POST'])]
    public function finalize(Request $req): JsonResponse
    {
        if ($res = $this->requirePerm($req, 'mesh.finalize')) {
            return $res;
        }
        $id = (int) $req->get('proposal_id', 0);
        $quorum = (int) $req->get('quorum', 3);

        // TTL 24h: expire старые предложения
        $row = $this->db->fetchAssociative('SELECT ts FROM policy_mesh_proposals WHERE id=?', [$id]);
        if ($row) {
            $ts = strtotime((string) $row['ts']);
            if ($ts < time() - 86400) {
                $this->db->update('policy_mesh_proposals', ['status' => 'expired', 'applied_at' => gmdate('c')], ['id' => $id]);

                return new JsonResponse(['ok' => false, 'status' => 'expired']);
            }
        }

        $res = $this->engine->tryFinalize($id, $quorum);
        // audit record (можно отправлять в Trust Ledger через outbox)
        $this->db->insert('policy_mesh_audit', [
            'ts' => gmdate('c'),
            'proposal_id' => $id,
            'result' => json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return new JsonResponse($res);
    }

    #[Route('/api/policy/mesh/secure/map', methods: ['GET'])]
    public function map(Request $req): JsonResponse
    {
        if ($res = $this->requirePerm($req, 'mesh.read')) {
            return $res;
        }

        return new JsonResponse(['ok' => true, 'lww_map' => $this->map->export()]);
    }
}
