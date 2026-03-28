<?php

declare(strict_types=1);

namespace App\Component\Product\Traffic\Controller;

use App\Component\Product\Traffic\PriorityQueueService;
use App\Component\Product\Traffic\PriorityScheduler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TrafficAPI
{
    public function __construct(private readonly PriorityQueueService $queue, private readonly PriorityScheduler $sched)
    {
    }

    #[Route('/api/traffic/enqueue', methods: ['POST'])]
    public function enqueue(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $task = (string) ($req->get('task') ?? 'llm.call');
        $priority = (int) ($req->get('priority') ?? 1);
        $id = $this->queue->enqueue($tenant, $task, $priority);

        return new JsonResponse(['ok' => true, 'id' => $id]);
    }

    #[Route('/api/traffic/next', methods: ['POST'])]
    public function next(): JsonResponse
    {
        $item = $this->sched->next();

        return new JsonResponse(['item' => $item]);
    }
}
