<?php

declare(strict_types=1);

namespace App\Component\Product\AutoRemediation\Controller;

use App\Component\Product\AutoRemediation\AutoActionExecutor;
use App\Component\Product\AutoRemediation\RemediationEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AutoRemediationAPI
{
    public function __construct(private readonly RemediationEngine $engine, private readonly AutoActionExecutor $exec)
    {
    }

    #[Route('/api/policy/remediation/run', methods: ['POST'])]
    public function run(Request $req): JsonResponse
    {
        $aggregate = json_decode((string) $req->get('aggregate', '{}'), true) ?: [];
        $insights = json_decode((string) $req->get('insights', '[]'), true) ?: [];
        $actions = $this->engine->plan($aggregate, $insights);

        $results = [];
        foreach ($actions as $a) {
            $res = $this->exec->execute($a);
            $results[] = ['action' => $a['action'], 'result' => $res];
        }

        return new JsonResponse(['ok' => true, 'planned' => $actions, 'results' => $results]);
    }
}
