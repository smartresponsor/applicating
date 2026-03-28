<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI;

use App\Component\Product\AI\ABI\Action\BusinessActionOrchestrator;
use App\Component\Product\AI\ABI\Forecast\RevenueForecast;
use App\Component\Product\AI\ABI\Policy\BusinessPolicyEngine;
use Doctrine\DBAL\Connection;

final class ABIRunner
{
    public function __construct(
        private readonly Connection $db,
        private readonly RevenueForecast $forecast,
        private readonly BusinessPolicyEngine $policy,
        private readonly BusinessActionOrchestrator $actions,
    ) {
    }

    /**
     * @param array<int,float> $revenueSeries
     *
     * @return array<string,mixed>
     */
    public function runOnce(string $tenantId, array $revenueSeries, float $margin, string $priority = 'standard'): array
    {
        $f = $this->forecast->predict($revenueSeries);
        $decision = $this->policy->decide($f, ['margin' => $margin, 'tenant_priority' => $priority]);
        $apply = $this->actions->apply($tenantId, $decision['decision'], $decision['params']);
        $event = [
            'ts' => gmdate('c'),
            'tenant_id' => $tenantId,
            'revenue_forecast' => $f['predicted'],
            'action' => $decision['decision'],
            'params' => $decision['params'],
            'outcome' => $apply['ok'] ? 'applied' : 'failed',
            'updated' => $apply['updated'],
            'details' => ['avg' => $f['avg'], 'slope' => $f['slope'], 'series' => $revenueSeries],
        ];
        $this->db->insert('abi_audit_events', [
            'ts' => $event['ts'],
            'tenant_id' => $tenantId,
            'revenue_forecast' => $f['predicted'],
            'action' => $decision['decision'],
            'outcome' => $event['outcome'],
            'details' => json_encode($event['details'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $event;
    }
}
