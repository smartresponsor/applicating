<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\Controller;

use App\Component\Product\Compliance\Consent\ConsentRegistry;
use App\Component\Product\Compliance\Controls\ControlMapper;
use App\Component\Product\Compliance\Controls\PolicyCatalog;
use App\Component\Product\Compliance\DPIA\DPIAEngine;
use App\Component\Product\Compliance\Retention\DataRetentionManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ComplianceAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly PolicyCatalog $catalog,
        private readonly ControlMapper $mapper,
        private readonly ConsentRegistry $consent,
        private readonly DataRetentionManager $retention,
        private readonly DPIAEngine $dpia,
    ) {
    }

    #[Route('/api/compliance/policies', methods: ['GET'])]
    public function policies(): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'catalog' => $this->catalog->list()]);
    }

    #[Route('/api/compliance/map', methods: ['GET'])]
    public function map(Request $req): JsonResponse
    {
        $std = (string) $req->get('standard', 'gdpr');

        return new JsonResponse(['ok' => true, 'map' => $this->mapper->map($std)]);
    }

    #[Route('/api/compliance/consent/set', methods: ['POST'])]
    public function consentSet(Request $req): JsonResponse
    {
        $this->consent->set((string) $req->get('subject'), (string) $req->get('purpose'), (bool) $req->get('granted', true));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/compliance/consent/get', methods: ['GET'])]
    public function consentGet(Request $req): JsonResponse
    {
        return new JsonResponse(['ok' => true, 'consent' => $this->consent->get((string) $req->get('subject'))]);
    }

    #[Route('/api/compliance/retention/schedule', methods: ['POST'])]
    public function retentionSchedule(Request $req): JsonResponse
    {
        $this->retention->schedule((string) $req->get('dataset'), (int) $req->get('days', 30));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/compliance/retention/sweep', methods: ['POST'])]
    public function retentionSweep(): JsonResponse
    {
        $n = $this->retention->sweep();

        return new JsonResponse(['ok' => true, 'deleted' => $n]);
    }

    #[Route('/api/compliance/dpia/evaluate', methods: ['POST'])]
    public function dpiaEvaluate(Request $req): JsonResponse
    {
        $proc = (string) $req->get('process', 'default');
        $f = json_decode((string) $req->get('factors', '{}'), true) ?: [];

        return new JsonResponse(['ok' => true, 'dpia' => $this->dpia->evaluate($proc, $f)]);
    }
}
