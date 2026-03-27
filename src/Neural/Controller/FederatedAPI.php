<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\Controller;

use App\Component\Product\Neural\FLP\FederatedSyncService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FederatedAPI
{
    public function __construct(private readonly FederatedSyncService $flp)
    {
    }

    #[Route('/api/neural/fabric/sync', methods: ['POST'])]
    public function sync(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $out = $this->flp->collectAndSign($tenant);

        return new JsonResponse(['ok' => true, 'outbound' => $out]);
    }

    #[Route('/api/neural/fabric/receive', methods: ['POST'])]
    public function receive(Request $req): JsonResponse
    {
        $payload = (string) ($req->get('payload') ?? '{}');
        $signature = (string) ($req->get('signature') ?? '');
        $ok = $this->flp->receive($payload, $signature);

        return new JsonResponse(['ok' => $ok]);
    }
}
