<?php

declare(strict_types=1);

namespace App\Component\Product\AI\Controller;

use App\Component\Product\AI\AnomalyDetector;
use App\Component\Product\AI\GovernanceAI;
use App\Component\Product\AI\OrchestratorAI;
use App\Component\Product\AI\PolicyRewriter;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AIGovernanceAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly OrchestratorAI $oai,
        private readonly GovernanceAI $gai,
        private readonly AnomalyDetector $det,
        private readonly PolicyRewriter $rew,
    ) {
    }

    #[Route('/api/ai/autoscale', methods: ['POST'])]
    public function autoscale(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $current = (int) ($req->get('replicas') ?? 1);
        $desired = $this->oai->desiredReplicas($tenant, $current);

        return new JsonResponse(['ok' => true, 'desired_replicas' => $desired]);
    }

    #[Route('/api/ai/recommend/limit', methods: ['GET'])]
    public function limit(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $limit = $this->gai->recommendLimit($tenant);

        return new JsonResponse(['tenant' => $tenant, 'recommended_monthly_limit_usd' => $limit]);
    }

    #[Route('/api/ai/scan', methods: ['GET'])]
    public function scan(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');

        return new JsonResponse($this->det->scan($tenant));
    }

    #[Route('/api/ai/policy/apply', methods: ['POST'])]
    public function apply(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $scan = $this->det->scan($tenant);
        $this->rew->rewrite($tenant, (bool) ($scan['anomaly'] ?? false));

        return new JsonResponse(['ok' => true, 'anomaly' => $scan['anomaly'] ?? false]);
    }
}
