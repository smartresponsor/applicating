<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow\Controller;

use App\Component\Product\PolicyFlow\Connectors\BillingConnector;
use App\Component\Product\PolicyFlow\Connectors\RateConnector;
use App\Component\Product\PolicyFlow\Connectors\TrustConnector;
use App\Component\Product\PolicyFlow\PolicyFlowEngine;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PolicyOrchestratorAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly PolicyFlowEngine $flow,
        private readonly BillingConnector $billing,
        private readonly TrustConnector $trust,
        private readonly RateConnector $rate,
    ) {
    }

    #[Route('/api/policy/flow/run', methods: ['POST'])]
    public function run(Request $req): JsonResponse
    {
        $this->flow->registerConnector('billing', $this->billing);
        $this->flow->registerConnector('trust', $this->trust);
        $this->flow->registerConnector('rate', $this->rate);

        $policy = (string) ($req->get('policy') ?? 'discount.vip');
        $ctx = [
            'tenant' => ['id' => (string) ($req->get('tenant') ?? 'tenantA'), 'plan' => (string) ($req->get('plan') ?? 'standard')],
            'qps' => (float) ($req->get('qps') ?? 0.0),
            'trustscore' => (float) ($req->get('trustscore') ?? 0.0),
        ];
        $res = $this->flow->run($policy, $ctx);

        return new JsonResponse($res);
    }

    #[Route('/api/policy/flow/status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $count = (int) ($this->db->fetchOne('SELECT COUNT(*) FROM policy_flow_log') ?? 0);

        return new JsonResponse(['flows_logged' => $count]);
    }
}
