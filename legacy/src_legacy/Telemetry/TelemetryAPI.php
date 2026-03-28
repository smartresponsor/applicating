<?php

declare(strict_types=1);

namespace App\Component\Product\Telemetry;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TelemetryAPI
{
    public function __construct(private readonly TelemetryCollector $collector)
    {
    }

    #[Route('/api/telemetry/report', methods: ['POST'])]
    public function report(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        if (null !== $req->get('qps')) {
            $this->collector->recordQps($tenant, (float) $req->get('qps'));
        }
        if (null !== $req->get('error_rate')) {
            $this->collector->recordErrorRate($tenant, (float) $req->get('error_rate'));
        }
        if (null !== $req->get('p95_ms')) {
            $this->collector->recordLatency($tenant, (float) $req->get('p95_ms'));
        }

        return new JsonResponse(['ok' => true]);
    }
}
