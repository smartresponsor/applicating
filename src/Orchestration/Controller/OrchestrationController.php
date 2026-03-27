<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration\Controller;

use App\Component\Product\Orchestration\ActionExecutor;
use App\Component\Product\Orchestration\DTO\EventDTO;
use App\Component\Product\Orchestration\EventBus;
use App\Component\Product\Orchestration\RuleEngine;
use App\Component\Product\Orchestration\TriggerRegistry;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class OrchestrationController
{
    public function __construct(
        private readonly Connection $db,
        private readonly EventBus $bus,
        private readonly TriggerRegistry $triggers,
        private readonly RuleEngine $rules,
        private readonly ActionExecutor $exec,
    ) {
    }

    #[Route('/api/orch/emit', methods: ['POST'])]
    public function emit(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent() ?: '{}', true) ?: [];
        $e = new EventDTO($data['type'] ?? 'demo', $data['tenant_id'] ?? 'tenantA', $data['payload'] ?? [], gmdate('c'));
        $id = $this->bus->emit($e);

        return new JsonResponse(['ok' => true, 'id' => $id]);
    }

    #[Route('/api/orch/run', methods: ['POST'])]
    public function run(): JsonResponse
    {
        $queued = $this->bus->fetchQueued(50);
        $results = [];
        foreach ($queued as $row) {
            $event = [
                'id' => $row['id'],
                'tenant_id' => $row['tenant_id'],
                'type' => $row['type'],
                'payload' => json_decode($row['payload'] ?? '{}', true),
            ];
            $applied = [];
            foreach (($this->triggers->rules()[$event['type']] ?? []) as $rule) {
                if ($this->rules->match($rule['rule'], $event, $rule['params'])) {
                    $applied[] = [
                        'rule' => $rule['rule'],
                        'action' => $rule['action'],
                        'result' => $this->exec->execute($rule['action'], $event, $rule['params']),
                    ];
                }
            }
            $this->bus->markProcessed((int) $row['id'], 'processed', json_encode($applied, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $results[] = ['event_id' => $row['id'], 'applied' => $applied];
        }

        return new JsonResponse(['ok' => true, 'processed' => count($results), 'results' => $results]);
    }
}
