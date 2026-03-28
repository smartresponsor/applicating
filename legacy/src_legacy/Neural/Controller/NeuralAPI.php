<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\Controller;

use App\Component\Product\Neural\Agent\TenantAgent;
use App\Component\Product\Neural\FabricHub;
use App\Component\Product\Neural\Training\TrainingPipeline;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class NeuralAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly TenantAgent $agent,
        private readonly FabricHub $hub,
        private readonly TrainingPipeline $train,
    ) {
    }

    #[Route('/api/neural/agent/decide', methods: ['POST'])]
    public function decide(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $res = $this->agent->decide($tenant);

        return new JsonResponse($res);
    }

    #[Route('/api/neural/fabric/aggregate', methods: ['POST'])]
    public function aggregate(): JsonResponse
    {
        $adj = $this->hub->aggregate();
        $this->hub->broadcast($adj);

        return new JsonResponse(['ok' => true, 'global_adjust' => $adj]);
    }

    #[Route('/api/neural/train/save', methods: ['POST'])]
    public function save(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $w = (float) ($req->get('w') ?? 1.0);
        $this->train->saveLocalWeights($tenant, $w);

        return new JsonResponse(['ok' => true]);
    }
}
