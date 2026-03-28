<?php

declare(strict_types=1);

namespace App\Component\Product\FederationLearning\Controller;

use App\Component\Product\FederationLearning\CrossRegionSync;
use App\Component\Product\FederationLearning\FederationLearner;
use App\Component\Product\FederationLearning\Security\MetricsSigner;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FederationLearningAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly FederationLearner $learner,
        private readonly CrossRegionSync $sync,
        private readonly MetricsSigner $signer,
    ) {
    }

    #[Route('/api/policy/federation/learning/export', methods: ['GET'])]
    public function export(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy', 'discount.vip');
        $data = $this->sync->exportPolicyStats($policy);
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sig = $this->signer->sign($payload);

        return new JsonResponse(['payload' => $payload, 'signature' => $sig]);
    }

    #[Route('/api/policy/federation/learning/apply', methods: ['POST'])]
    public function apply(Request $req): JsonResponse
    {
        $payload = (string) $req->get('payload', '{}');
        $sig = (string) $req->get('signature', '');
        if (!$this->signer->verify($payload, $sig)) {
            return new JsonResponse(['ok' => false, 'error' => 'invalid_signature'], 401);
        }
        $data = json_decode($payload, true) ?: [];
        $policy = (string) ($data['policy'] ?? 'discount.vip');
        // Переводим формат stats {variant:{wins,trials}} -> региональная проекция
        $regional = [];
        foreach (($data['stats'] ?? []) as $variant => $s) {
            $regional['remote'] = ['variant' => $variant, 'wins' => (int) ($s['wins'] ?? 0), 'trials' => (int) ($s['trials'] ?? 0)];
        }
        $res = $this->learner->applyGlobalUpdate($policy, $regional);

        return new JsonResponse($res);
    }
}
