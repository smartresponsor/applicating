<?php

declare(strict_types=1);

namespace App\Component\Product\GraphQL\Controller;

use App\Component\Product\GraphQL\Federation\QueryCostLimiter;
use App\Component\Product\GraphQL\Federation\SchemaComposer;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GatewayController
{
    public function __construct(
        private readonly Connection $db,
        private readonly SchemaComposer $composer,
        private readonly QueryCostLimiter $qcost,
    ) {
    }

    #[Route('/api/graphql/schema', methods: ['GET'])]
    public function schema(): JsonResponse
    {
        $gw = file_get_contents(__DIR__.'/../Schema/schema.graphqls') ?: '';
        $sdl = $this->composer->compose($gw);

        return new JsonResponse(['sdl' => $sdl]);
    }

    #[Route('/api/graphql', methods: ['POST'])]
    public function execute(Request $req): JsonResponse
    {
        $query = (string) ($req->get('query') ?? '');
        $vars = $req->get('variables');
        $cost = $this->qcost->cost($query);
        $this->qcost->enforce($cost);

        // Демонстрационная обработка основных полей
        if (str_contains($query, 'health')) {
            return new JsonResponse(['data' => ['health' => 'ok', '_cost' => $cost]]);
        }
        if (str_contains($query, 'abiForecast')) {
            return new JsonResponse(['data' => ['abiForecast' => ['tenant' => 'tenantA', 'predictedRevenue' => 123.45, 'model' => 'demo', 'ts' => gmdate('c')], '_cost' => $cost]]);
        }
        if (str_contains($query, 'orchestrationEmit')) {
            // Пишем в orchestration_events
            $tenant = is_array($vars) && isset($vars['tenant']) ? (string) $vars['tenant'] : 'tenantA';
            $type = is_array($vars) && isset($vars['type']) ? (string) $vars['type'] : 'demo.event';
            $payload = is_array($vars) && isset($vars['payload']) ? json_encode($vars['payload']) : '{}';
            $this->db->insert('orchestration_events', [
                'ts' => gmdate('c'), 'tenant_id' => $tenant, 'type' => $type, 'payload' => $payload, 'status' => 'queued',
            ]);

            return new JsonResponse(['data' => ['orchestrationEmit' => ['ok' => true, 'id' => (int) $this->db->lastInsertId()], '_cost' => $cost]]);
        }

        return new JsonResponse(['data' => null, 'errors' => [['message' => 'Not implemented', 'cost' => $cost]]], 400);
    }
}
