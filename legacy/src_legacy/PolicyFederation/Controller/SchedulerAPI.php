<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation\Controller;

use App\Component\Product\PolicyFederation\Scheduler\CanaryController;
use App\Component\Product\PolicyFederation\Scheduler\PolicyFederationScheduler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class SchedulerAPI
{
    public function __construct(private readonly PolicyFederationScheduler $sched, private readonly CanaryController $canary)
    {
    }

    #[Route('/api/policy/federation/scheduler/tick', methods: ['POST'])]
    public function tick(): JsonResponse
    {
        $res = $this->sched->tick($this->canary->getPercent('tenant.policy', 1));

        return new JsonResponse($res);
    }
}
