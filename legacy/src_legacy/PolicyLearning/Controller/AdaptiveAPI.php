<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyLearning\Controller;

use App\Component\Product\PolicyLearning\AdaptiveLearner;
use App\Component\Product\PolicyLearning\FeedbackIngestor;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AdaptiveAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly AdaptiveLearner $learner,
        private readonly FeedbackIngestor $feedback,
    ) {
    }

    #[Route('/api/policy/learning/variant/define', methods: ['POST'])]
    public function define(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy');
        $variant = (string) $req->get('variant');
        $effects = json_decode((string) $req->get('effects', '{}'), true) ?: [];
        $this->learner->getOrCreateVariant($policy, $variant, $effects);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/policy/learning/assign', methods: ['POST'])]
    public function assign(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy');
        $tenant = (string) $req->get('tenant', 'tenantA');
        $v = $this->learner->chooseVariant($policy);
        if (!$v) {
            return new JsonResponse(['ok' => false, 'error' => 'no_variants'], 400);
        }
        $this->db->insert('policy_assignment', [
            'ts' => gmdate('c'), 'policy' => $policy, 'variant' => $v['variant_id'], 'tenant_id' => $tenant,
        ]);
        $effects = $this->learner->effectsForVariant($policy, $v['variant_id']);

        return new JsonResponse(['ok' => true, 'variant' => $v['variant_id'], 'effects' => $effects]);
    }

    #[Route('/api/policy/learning/feedback', methods: ['POST'])]
    public function feedback(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy');
        $variant = (string) $req->get('variant');
        $tenant = (string) $req->get('tenant', 'tenantA');
        $reward = (float) $req->get('reward', 0);
        $ctx = json_decode((string) $req->get('context', '{}'), true) ?: [];
        $this->feedback->record($policy, $variant, $tenant, $reward, $ctx);
        $this->learner->updatePosterior($policy, $variant, $reward);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/policy/learning/status', methods: ['GET'])]
    public function status(Request $req): JsonResponse
    {
        $policy = (string) $req->get('policy');
        $rows = $this->db->fetchAllAssociative('SELECT variant_id, wins, trials, alpha, beta FROM policy_variant WHERE policy=?', [$policy]);

        return new JsonResponse(['policy' => $policy, 'variants' => $rows]);
    }
}
