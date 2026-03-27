<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration;

use App\Component\Product\Orchestration\Integration\GuardBridge;
use Doctrine\DBAL\Connection;

final class ActionExecutor
{
    public function __construct(private readonly Connection $db, private readonly GuardBridge $guard)
    {
    }

    /** @param array<string,mixed> $event @param array<string,mixed> $params */
    public function execute(string $action, array $event, array $params): array
    {
        $tenant = (string) ($event['tenant_id'] ?? 'tenantA');

        // Определяем риск (упрощённо)
        $risk = 'low';
        if ('pricing.adjust' === $action && (($params['max_pct'] ?? 0.0) > 0.1)) {
            $risk = 'high';
        }
        if ('credit.issue' === $action && (($event['payload']['credit_cents'] ?? 0) > 3000)) {
            $risk = 'medium';
        }

        // Если риск medium/high → ставим в approval_requests и выходим
        if ('low' != $risk) {
            $id = $this->guard->maybeEnqueue($tenant, $action, $params, $risk, ['event' => $event]);

            return ['ok' => true, 'queued_for_approval' => $id, 'risk' => $risk];
        }

        // Иначе исполняем сразу (демо)
        switch ($action) {
            case 'pricing.adjust':
                $pct = (float) ($params['max_pct'] ?? 0.03);
                $n = $this->db->executeStatement(
                    'UPDATE pricing SET base_price_cents = CAST(base_price_cents * (1 + :pct) AS INT) WHERE tenant_id = :t',
                    ['pct' => $pct, 't' => $tenant]
                );

                return ['ok' => true, 'updated' => $n, 'risk' => $risk];
            case 'campaign.winback':
                $this->db->insert('campaign_events', [
                    'ts' => gmdate('c'),
                    'tenant_id' => $tenant,
                    'type' => 'winback',
                    'payload' => json_encode(['discount_pct' => $params['discount_pct'] ?? 10], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                return ['ok' => true, 'queued' => 1, 'risk' => $risk];
            case 'credit.issue':
                $this->db->insert('credits', [
                    'tenant_id' => $tenant,
                    'credit_cents' => min((int) ($event['payload']['credit_cents'] ?? 2000), (int) ($params['cap_cents'] ?? 5000)),
                    'reason' => 'SLA breach (orchestrated)',
                ]);

                return ['ok' => true, 'issued' => 1, 'risk' => $risk];
            default:
                return ['ok' => false, 'error' => 'unknown action', 'risk' => $risk];
        }
    }
}
