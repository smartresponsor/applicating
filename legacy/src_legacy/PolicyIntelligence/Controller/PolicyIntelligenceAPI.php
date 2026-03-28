<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyIntelligence\Controller;

use App\Component\Product\PolicyIntelligence\Insights\InsightEngine;
use App\Component\Product\PolicyIntelligence\SignalAggregator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PolicyIntelligenceAPI
{
    public function __construct(private readonly SignalAggregator $agg, private readonly InsightEngine $engine)
    {
    }

    #[Route('/api/policy/intelligence/aggregate', methods: ['GET'])]
    public function aggregate(Request $req): JsonResponse
    {
        $win = (int) $req->get('window', 60);

        return new JsonResponse(['ok' => true, 'aggregate' => $this->agg->aggregate($win)]);
    }

    #[Route('/api/policy/intelligence/insights', methods: ['GET'])]
    public function insights(Request $req): JsonResponse
    {
        $win = (int) $req->get('window', 60);
        $agg = $this->agg->aggregate($win);

        return new JsonResponse(['ok' => true, 'aggregate' => $agg, 'insights' => $this->engine->derive($agg)]);
    }
}
