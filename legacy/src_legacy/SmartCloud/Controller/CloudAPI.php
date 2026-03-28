<?php

declare(strict_types=1);

namespace App\Component\Product\SmartCloud\Controller;

use App\Component\Product\SmartCloud\AutoProvisioner;
use App\Component\Product\SmartCloud\BillingDaemon;
use App\Component\Product\SmartCloud\Orchestrator;
use App\Component\Product\SmartCloud\TenantService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CloudAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly TenantService $tenants,
        private readonly Orchestrator $orch,
        private readonly BillingDaemon $bill,
        private readonly AutoProvisioner $prov,
    ) {
    }

    #[Route('/api/cloud/tenant/register', methods: ['POST'])]
    public function register(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $plan = (string) ($req->get('plan') ?? 'standard');
        $region = (string) ($req->get('region') ?? 'us-east');
        $this->tenants->register($tenant, $plan, $region);
        $this->prov->bootstrap($tenant);

        return new JsonResponse(['ok' => true, 'tenant' => $tenant]);
    }

    #[Route('/api/cloud/tenant/limit', methods: ['POST'])]
    public function limit(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $usd = (float) ($req->get('monthly_limit_usd') ?? 100.0);
        $qps = (int) ($req->get('qps_limit') ?? 50);
        $this->tenants->setLimit($tenant, $usd, $qps);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/cloud/usage/collect', methods: ['POST'])]
    public function usage(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $feature = (string) ($req->get('feature') ?? 'llm.call');
        $qty = (int) ($req->get('quantity') ?? 1);
        $price = (float) ($req->get('unit_price_usd') ?? 0.002);
        $this->db->insert('tenant_usage', [
            'ts' => gmdate('c'), 'tenant_id' => $tenant, 'feature' => $feature,
            'quantity' => $qty, 'unit_price_usd' => $price, 'amount_usd' => $qty * $price,
        ]);

        return new JsonResponse(['ok' => true, 'amount_usd' => $qty * $price]);
    }

    #[Route('/api/cloud/invoice/generate', methods: ['POST'])]
    public function invoice(): JsonResponse
    {
        $count = $this->bill->daily();

        return new JsonResponse(['ok' => true, 'generated' => $count]);
    }

    #[Route('/api/cloud/emit', methods: ['POST'])]
    public function emit(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $type = (string) ($req->get('type') ?? 'demo.event');
        $id = $this->orch->emit($tenant, $type, ['source' => 'smartcloud']);

        return new JsonResponse(['ok' => true, 'event_id' => $id]);
    }

    #[Route('/api/cloud/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'ts' => gmdate('c')]);
    }
}
