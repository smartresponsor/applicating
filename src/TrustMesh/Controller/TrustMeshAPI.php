<?php

declare(strict_types=1);

namespace App\Component\Product\TrustMesh\Controller;

use App\Component\Product\TrustMesh\Security\SecureBridge;
use App\Component\Product\TrustMesh\TrustMeshService;
use App\Component\Product\TrustMesh\TrustScoreAggregator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TrustMeshAPI
{
    public function __construct(
        private readonly TrustMeshService $svc,
        private readonly TrustScoreAggregator $agg,
        private readonly SecureBridge $sec,
    ) {
    }

    #[Route('/api/trustmesh/score/report', methods: ['POST'])]
    public function report(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $sla = (float) ($req->get('sla') ?? 0.99);
        $refund = (float) ($req->get('refund_rate') ?? 0.01);
        $uptime = (float) ($req->get('uptime') ?? 0.999);
        $this->svc->report($tenant, $sla, $refund, $uptime);
        $score = $this->agg->compute($tenant);

        return new JsonResponse(['ok' => true, 'score' => $score]);
    }

    #[Route('/api/trustmesh/aggregate', methods: ['GET'])]
    public function latest(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');

        return new JsonResponse(['tenant' => $tenant, 'score' => $this->agg->latest($tenant)]);
    }

    #[Route('/api/trustmesh/bridge/verify', methods: ['POST'])]
    public function verify(Request $req): JsonResponse
    {
        $payload = (string) ($req->get('payload') ?? '{}');
        $sig = (string) ($req->get('signature') ?? '');

        return new JsonResponse(['valid' => $this->sec->verify($payload, $sig)]);
    }
}
