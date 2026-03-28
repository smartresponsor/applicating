<?php

declare(strict_types=1);

namespace App\Component\Product\Policy\Controller;

use App\Component\Product\Policy\PolicyEngine;
use App\Component\Product\Policy\PolicyRegistry;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PolicyAPI
{
    public function __construct(private readonly Connection $db, private readonly PolicyRegistry $reg, private readonly PolicyEngine $eng)
    {
    }

    #[Route('/api/policy/eval', methods: ['POST'])]
    public function eval(Request $req): JsonResponse
    {
        $name = (string) ($req->get('name') ?? 'tenant.policy');
        $ctx = $req->getPayload() ?? $req->request->all();
        $row = $this->reg->latest($name);
        $policies = $row ? json_decode($row['content'], true) : ['policies' => []];
        $res = $this->eng->evaluate($policies['policies'] ?? [], (array) $ctx);
        $this->reg->audit($name, (string) ($ctx['subject'] ?? 'api'), $res['allow'] ? 'allow' : 'deny', $ctx);

        return new JsonResponse($res);
    }

    #[Route('/api/policy/reload', methods: ['POST'])]
    public function reload(Request $req): JsonResponse
    {
        $name = (string) ($req->get('name') ?? 'tenant.policy');
        $content = (string) ($req->get('content') ?? '{"policies":[]}');
        $fmt = (string) ($req->get('format') ?? 'json');
        $id = $this->reg->register($name, $content, $fmt);

        return new JsonResponse(['ok' => true, 'id' => $id]);
    }
}
