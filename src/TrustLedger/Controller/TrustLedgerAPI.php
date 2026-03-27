<?php

declare(strict_types=1);

namespace App\Component\Product\TrustLedger\Controller;

use App\Component\Product\TrustLedger\Security\LedgerSigner;
use App\Component\Product\TrustLedger\TrustLedger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TrustLedgerAPI
{
    public function __construct(private readonly TrustLedger $ledger, private readonly LedgerSigner $signer)
    {
    }

    #[Route('/api/trust-ledger/head', methods: ['GET'])]
    public function head(): JsonResponse
    {
        $h = $this->ledger->head();

        return new JsonResponse(['head' => $h]);
    }

    #[Route('/api/trust-ledger/append', methods: ['POST'])]
    public function append(Request $req): JsonResponse
    {
        $payload = (string) $req->get('payload', '{}');
        $sig = (string) $req->get('signature', '');
        if (!$this->signer->verify($payload, $sig)) {
            return new JsonResponse(['ok' => false, 'error' => 'invalid_signature'], 401);
        }
        $res = $this->ledger->append($payload, $sig);

        return new JsonResponse($res);
    }

    #[Route('/api/trust-ledger/verify', methods: ['GET'])]
    public function verify(Request $req): JsonResponse
    {
        $depth = (int) $req->get('depth', 1000);
        $res = $this->ledger->verifyChain($depth);

        return new JsonResponse($res);
    }
}
