<?php

declare(strict_types=1);

namespace App\Component\Product\Sentient\Controller;

use App\Component\Product\Sentient\AnomalyPredictor;
use App\Component\Product\Sentient\DecisionTrainer;
use App\Component\Product\Sentient\FeedbackIngestor;
use App\Component\Product\Sentient\SelfCorrectionEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class SentientAPI
{
    public function __construct(
        private readonly FeedbackIngestor $ingest,
        private readonly AnomalyPredictor $predictor,
        private readonly DecisionTrainer $trainer,
        private readonly SelfCorrectionEngine $corrector,
    ) {
    }

    #[Route('/api/intelligence/feedback', methods: ['POST'])]
    public function feedback(Request $req): JsonResponse
    {
        $this->ingest->ingest(
            (string) $req->get('policy', 'discount.vip'),
            (string) $req->get('region', 'us'),
            (float) $req->get('sla_ok', 1.0),
            (float) $req->get('latency_ms', 300.0),
            (float) $req->get('risk', 0.4),
            (float) $req->get('trust', 0.6)
        );

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/intelligence/predict', methods: ['GET'])]
    public function predict(Request $req): JsonResponse
    {
        $min = (int) $req->get('window', 60);

        return new JsonResponse(['ok' => true, 'prediction' => $this->predictor->predict($min)]);
    }

    #[Route('/api/intelligence/train', methods: ['POST'])]
    public function train(Request $req): JsonResponse
    {
        $min = (int) $req->get('window', 180);

        return new JsonResponse(['ok' => true, 'thresholds' => $this->trainer->train($min)]);
    }

    #[Route('/api/intelligence/adjust', methods: ['POST'])]
    public function adjust(Request $req): JsonResponse
    {
        $ths = json_decode((string) $req->get('thresholds', '{}'), true) ?: [];

        return new JsonResponse($this->corrector->adjust($ths));
    }
}
