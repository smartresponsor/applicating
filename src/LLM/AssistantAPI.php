<?php

declare(strict_types=1);

namespace App\Component\Product\LLM;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AssistantAPI
{
    public function __construct(
        private readonly ContextBuilder $ctx,
        private readonly PluginManager $pm,
        private readonly DecisionLogger $log,
    ) {
    }

    #[Route('/api/assistant/list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $out = array_map(fn ($p) => $p->metadata(), $this->pm->all());

        return new JsonResponse(['plugins' => $out]);
    }

    #[Route('/api/assistant/run', methods: ['POST'])]
    public function run(Request $req): JsonResponse
    {
        $tenant = $req->get('tenant') ?: 'tenantA';
        $prompt = (string) ($req->get('prompt') ?: 'auto');
        $ctx = $this->ctx->build($tenant);
        $results = [];
        foreach ($this->pm->all() as $p) {
            $decisions = $p->analyze($ctx);
            foreach ($decisions as $d) {
                $this->log->log($tenant, $p->metadata()['name'] ?? 'advisor', $prompt, $d, 'llm');
                $results[] = ['advisor' => $p->metadata(), 'decision' => $d];
            }
        }

        return new JsonResponse(['tenant' => $tenant, 'results' => $results]);
    }
}
