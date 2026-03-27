<?php

declare(strict_types=1);

namespace App\Component\Product\Predictive\Controller;

use App\Component\Product\Predictive\ML\SimpleRegressor;
use App\Component\Product\Predictive\OutcomeScorer;
use App\Component\Product\Predictive\PolicySimulator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class WhatIfAPI
{
    public function __construct(
        private readonly PolicySimulator $sim,
        private readonly OutcomeScorer $scorer,
        private readonly SimpleRegressor $ml,
    ) {
    }

    #[Route('/api/policy/predictive/whatif', methods: ['POST'])]
    public function whatif(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy', 'discount.vip');
        $effects = json_decode((string) $req->get('effects', '{}'), true) ?: [];
        $runs = (int) $req->get('runs', 1000);
        $sim = $this->sim->simulate($policy, $effects, $runs);
        $score = $this->scorer->score($sim);

        return new JsonResponse(['ok' => true, 'sim' => $sim, 'score' => $score]);
    }

    #[Route('/api/policy/predictive/mlscore', methods: ['POST'])]
    public function mlscore(Request $req): JsonResponse
    {
        $trust = (float) $req->get('trust', 0.5);
        $discount = (float) $req->get('discount', 0.0);
        $rate = (float) $req->get('rate_limit', 100.0);
        $y = $this->ml->predict($trust, $discount, $rate);

        return new JsonResponse(['ok' => true, 'ml_score' => $y]);
    }
}
