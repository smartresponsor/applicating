<?php

declare(strict_types=1);

namespace App\Component\Product\Cloud\Autoscale;

use App\Component\Product\AI\OrchestratorAI;

final class AutoScalerService
{
    public function __construct(private readonly OrchestratorAI $oai, private readonly K8sClient $k8s)
    {
    }

    public function reconcile(string $tenantId, string $deployment, int $currentReplicas): int
    {
        $desired = $this->oai->desiredReplicas($tenantId, $currentReplicas);
        if ($desired !== $currentReplicas) {
            $this->k8s->scale($deployment, $desired);
        }

        return $desired;
    }
}
