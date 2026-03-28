<?php

declare(strict_types=1);

namespace App\Component\Product\Dashboard\Controller;

use App\Component\Product\Dashboard\Service\ChartDataBuilder;
use App\Component\Product\Dashboard\Service\DashboardAggregator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController
{
    public function __construct(
        private readonly DashboardAggregator $agg,
        private readonly ChartDataBuilder $charts,
    ) {
    }

    #[Route('/api/dashboard/kpi', methods: ['GET'])]
    public function kpi(): JsonResponse
    {
        $tenant = $_GET['tenant'] ?? 'tenantA';
        $kpi = $this->agg->kpi($tenant);
        $data = array_map(fn ($m) => ['key' => $m->key, 'label' => $m->label, 'value' => $m->value, 'unit' => $m->unit], $kpi);

        return new JsonResponse(['tenant' => $tenant, 'kpi' => $data]);
    }

    #[Route('/api/dashboard/charts', methods: ['GET'])]
    public function charts(): JsonResponse
    {
        $tenant = $_GET['tenant'] ?? 'tenantA';
        $charts = $this->agg->charts($tenant);

        return new JsonResponse(['tenant' => $tenant, 'charts' => json_decode($this->charts->toJson($charts), true)]);
    }
}
