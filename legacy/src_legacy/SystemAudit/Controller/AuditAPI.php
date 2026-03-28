<?php

declare(strict_types=1);

namespace App\Component\Product\SystemAudit\Controller;

use App\Component\Product\SystemAudit\ImmutableLedgerWriter;
use App\Component\Product\SystemAudit\IntegrityMonitor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class AuditAPI
{
    public function __construct(
        private readonly IntegrityMonitor $monitor,
        private readonly ImmutableLedgerWriter $ledger,
    ) {
    }

    #[Route('/api/audit/log', methods: ['POST'])]
    public function log(Request $req): JsonResponse
    {
        $source = (string) $req->get('source', 'system');
        $event = (string) $req->get('event', 'unknown');
        $payload = json_decode((string) $req->get('payload', '{}'), true) ?: [];
        $this->monitor->log($source, $event, $payload);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/audit/append', methods: ['POST'])]
    public function append(Request $req): JsonResponse
    {
        $type = (string) $req->get('type', 'decision');
        $payload = json_decode((string) $req->get('payload', '{}'), true) ?: [];

        return new JsonResponse($this->ledger->append($type, $payload));
    }

    #[Route('/api/audit/check', methods: ['GET'])]
    public function check(Request $req): JsonResponse
    {
        $depth = (int) $req->get('depth', 1000);

        return new JsonResponse($this->ledger->verify($depth));
    }
}
