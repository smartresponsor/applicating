<?php

declare(strict_types=1);

namespace App\Controller\Federation;

use App\Federation\Product\ProductFederationService;
use App\Federation\Product\RegionCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ProductFederationController
{
    public function __construct(private ProductFederationService $svc)
    {
    }

    #[Route('/api/product/federation/{id}', methods: ['GET'])]
    public function context(int $id): JsonResponse
    {
        return new JsonResponse($this->svc->getContext($id));
    }

    #[Route('/api/product/federation/{id}/sync', methods: ['POST'])]
    public function sync(int $id): JsonResponse
    {
        return new JsonResponse($this->svc->sync($id));
    }

    #[Route('/api/product/federation/{id}/bind', methods: ['POST'])]
    public function bind(int $id, Request $req): JsonResponse
    {
        $d = $req->toArray();
        $fedId = (string) ($d['federationId'] ?? throw new \InvalidArgumentException('federationId required'));
        $tenant = (string) ($d['tenantId'] ?? throw new \InvalidArgumentException('tenantId required'));
        $region = isset($d['region']) ? RegionCode::from($d['region']) : null;
        $scope = (array) ($d['scope'] ?? []);
        $map = $this->svc->bind($id, $fedId, $tenant, $region, $scope);

        return new JsonResponse(['ok' => true, 'federationId' => $map->getFederationId()]);
    }
}
