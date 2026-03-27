<?php

declare(strict_types=1);

namespace App\Component\Product\RiskOrchestrator\Controller;

use App\Component\Product\RiskOrchestrator\PolicyOrchestrator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class OrchestratorAPI
{
    public function __construct(private readonly PolicyOrchestrator $orc)
    {
    }

    #[Route('/api/policy/orchestrator/evaluate', methods: ['POST'])]
    public function evaluate(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy', 'discount.vip');
        $region = (string) $req->get('region', 'us');
        $impact = (float) $req->get('impact', 0.5);
        $res = $this->orc->decide($policy, $region, $impact);

        return new JsonResponse(['ok' => true, 'result' => $res]);
    }
}
