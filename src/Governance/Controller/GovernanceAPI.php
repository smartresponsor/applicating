<?php

declare(strict_types=1);

namespace App\Component\Product\Governance\Controller;

use App\Component\Product\Governance\AdaptiveGovernor;
use App\Component\Product\Governance\Compliance\ComplianceWatcher;
use App\Component\Product\Governance\GovernanceEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GovernanceAPI
{
    public function __construct(
        private readonly GovernanceEngine $engine,
        private readonly AdaptiveGovernor $governor,
        private readonly ComplianceWatcher $compliance,
    ) {
    }

    #[Route('/api/policy/governance/evaluate', methods: ['POST'])]
    public function evaluate(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy', 'discount.vip');
        $region = (string) $req->get('region', 'us');
        $agg = $this->engine->aggregateDecision($policy, $region);
        $cmp = $this->compliance->checkWindow(60);

        return new JsonResponse(['ok' => true, 'aggregate' => $agg, 'compliance' => $cmp]);
    }

    #[Route('/api/policy/governance/enforce', methods: ['POST'])]
    public function enforce(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy', 'discount.vip');
        $meta = (float) $req->get('meta', 0.5);
        $res = $this->governor->enforceLimits($policy, $meta);

        return new JsonResponse($res);
    }
}
