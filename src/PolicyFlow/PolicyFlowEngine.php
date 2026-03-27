<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow;

use App\Component\Product\Policy\PolicyEngine;
use App\Component\Product\Policy\PolicyRegistry;

final class PolicyFlowEngine
{
    /** @var array<string,PolicyConnectorInterface> */
    private array $connectors = [];

    public function __construct(private readonly PolicyEngine $engine, private readonly PolicyRegistry $registry)
    {
    }

    public function registerConnector(string $name, PolicyConnectorInterface $connector): void
    {
        $this->connectors[$name] = $connector;
    }

    public function run(string $policyName, array $ctx): array
    {
        $row = $this->registry->latest($policyName);
        if (!$row) {
            return ['ok' => false, 'error' => 'policy_not_found'];
        }
        $data = json_decode($row['content'], true) ?: ['policies' => []];

        $candidates = array_values(array_filter($data['policies'] ?? [], fn ($p) => ($p['id'] ?? '') === $policyName));
        $policy = $candidates[0] ?? null;
        if (!$policy) {
            return ['ok' => false, 'error' => 'policy_not_found_in_file'];
        }

        $res = $this->engine->evaluate([$policy], $ctx);
        if (!$res['allow']) {
            return ['ok' => true, 'decision' => 'deny'];
        }

        $effects = [];
        foreach ($res['effects'] as $e) {
            if (str_contains($e, '=')) {
                [$k,$v] = array_map('trim', explode('=', $e, 2));
                $effects[$k] = is_numeric($v) ? (float) $v : $v;
            }
        }
        $ctx['effects'] = $effects;
        $ctx['policy'] = $policyName;

        $outputs = [];
        foreach (['trust', 'rate', 'billing'] as $name) {
            if (!isset($this->connectors[$name])) {
                continue;
            }
            $outputs[$name] = $this->connectors[$name]->execute($ctx);
        }

        return ['ok' => true, 'decision' => 'allow', 'effects' => $effects, 'outputs' => $outputs];
    }
}
