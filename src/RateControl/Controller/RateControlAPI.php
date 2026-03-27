<?php

declare(strict_types=1);

namespace App\Component\Product\RateControl\Controller;

use App\Component\Product\Envoy\EnvoyRateSync;
use App\Component\Product\RateControl\GlobalRateController;
use App\Component\Product\RateControl\QuarantineManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class RateControlAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly GlobalRateController $ctl,
        private readonly QuarantineManager $q,
        private readonly EnvoyRateSync $envoy,
    ) {
    }

    #[Route('/api/rate/mode', methods: ['GET'])]
    public function mode(): JsonResponse
    {
        return new JsonResponse(['mode' => $this->ctl->mode()]);
    }

    #[Route('/api/rate/quarantine/put', methods: ['POST'])]
    public function quarantine(Request $req): JsonResponse
    {
        $kind = (string) ($req->get('kind') ?? 'ip');
        $val = (string) ($req->get('value') ?? '0.0.0.0');
        $sec = (int) ($req->get('seconds') ?? 600);
        $this->q->put($kind, $val, $sec);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/rate/envoy/export', methods: ['POST'])]
    public function export(): JsonResponse
    {
        // Демонстрация: выгружаем статические правила
        $limits = [
            'standard' => ['unit' => 'second', 'requests_per_unit' => 20],
            'pro' => ['unit' => 'second', 'requests_per_unit' => 50],
            'vip' => ['unit' => 'second', 'requests_per_unit' => 100],
        ];
        $this->envoy->export($limits);

        return new JsonResponse(['ok' => true]);
    }
}
