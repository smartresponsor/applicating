<?php

declare(strict_types=1);

namespace App\Controller\Billing;

use App\Billing\Product\UsageOrchestrator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class BillingController
{
    public function __construct(private UsageOrchestrator $svc)
    {
    }

    #[Route('/api/billing/usage', methods: ['POST'])]
    public function usage(Request $req): JsonResponse
    {
        $d = $req->toArray();
        $tenant = (string) ($d['tenantId'] ?? throw new \InvalidArgumentException('tenantId required'));
        $productId = (int) ($d['productId'] ?? throw new \InvalidArgumentException('productId required'));
        $units = (float) ($d['units'] ?? throw new \InvalidArgumentException('units required'));
        $currency = (string) ($d['currency'] ?? 'USD');
        $trace = isset($d['traceId']) ? (string) $d['traceId'] : null;

        return new JsonResponse($this->svc->trackUsage($tenant, $productId, $units, $currency, $trace));
    }

    #[Route('/api/billing/invoice/settle', methods: ['POST'])]
    public function settle(Request $req): JsonResponse
    {
        $d = $req->toArray();
        $tenant = (string) ($d['tenantId'] ?? throw new \InvalidArgumentException('tenantId required'));
        $currency = (string) ($d['currency'] ?? 'USD');

        return new JsonResponse($this->svc->settleInvoice($tenant, $currency));
    }
}
