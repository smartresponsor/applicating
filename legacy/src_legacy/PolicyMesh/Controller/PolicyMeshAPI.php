<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Controller;

use App\Component\Product\PolicyMesh\Agent\RegionAgent;
use App\Component\Product\PolicyMesh\Consensus\ConsensusEngine;
use App\Component\Product\PolicyMesh\Consensus\LwwMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PolicyMeshAPI
{
    public function __construct(
        private readonly ConsensusEngine $engine,
        private readonly LwwMap $map,
        private readonly RegionAgent $agent,
    ) {
    }

    #[Route('/api/policy/mesh/propose', methods: ['POST'])]
    public function propose(Request $req): JsonResponse
    {
        $topic = (string) $req->get('topic', 'policy.params');
        $payload = json_decode((string) $req->get('payload', '{}'), true) ?: [];
        $region = (string) $req->get('region', 'local');
        $id = $this->engine->propose($topic, $payload, $region);

        return new JsonResponse(['ok' => true, 'proposal_id' => $id]);
    }

    #[Route('/api/policy/mesh/vote', methods: ['POST'])]
    public function vote(Request $req): JsonResponse
    {
        $id = (int) $req->get('proposal_id', 0);
        $region = (string) $req->get('region', 'local');
        $vote = (string) $req->get('vote', 'approve');
        $this->engine->vote($id, $region, $vote);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/policy/mesh/finalize', methods: ['POST'])]
    public function finalize(Request $req): JsonResponse
    {
        $id = (int) $req->get('proposal_id', 0);
        $quorum = (int) $req->get('quorum', 3);
        $res = $this->engine->tryFinalize($id, $quorum);

        return new JsonResponse($res);
    }

    #[Route('/api/policy/mesh/map', methods: ['GET'])]
    public function map(): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'lww_map' => $this->map->export()]);
    }

    #[Route('/api/policy/mesh/agent/status', methods: ['GET'])]
    public function agent(): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'agent' => $this->agent->status()]);
    }
}
