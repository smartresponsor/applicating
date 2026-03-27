<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing;

use App\Component\Product\SelfHealing\DTO\HealingAction;
use Doctrine\DBAL\Connection;

final class RecoveryExecutor
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function execute(string $tenantId, string $component, HealingAction $a): array
    {
        // Демонстрационные эффекты
        switch ($a->action) {
            case 'retry':
                $this->db->insert('healing_actions', [
                    'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'component' => $component,
                    'action' => 'retry', 'params' => json_encode($a->params),
                    'result' => 'queued', 'rule_applied' => $a->rule,
                ]);

                return ['ok' => true, 'queued' => true];
            case 'restart':
                $this->db->insert('healing_actions', [
                    'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'component' => $component,
                    'action' => 'restart', 'params' => json_encode($a->params),
                    'result' => 'done', 'rule_applied' => $a->rule,
                ]);

                return ['ok' => true, 'restart' => 'api_worker'];
            case 'rollback':
                $this->db->insert('healing_actions', [
                    'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'component' => $component,
                    'action' => 'rollback', 'params' => json_encode($a->params),
                    'result' => 'done', 'rule_applied' => $a->rule,
                ]);

                return ['ok' => true, 'rollback' => $a->params['to_version'] ?? 'stable'];
            case 'disable':
                $this->db->insert('healing_actions', [
                    'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'component' => $component,
                    'action' => 'disable', 'params' => json_encode($a->params),
                    'result' => 'done', 'rule_applied' => $a->rule,
                ]);

                return ['ok' => true, 'disabled' => true];
            case 'notify':
                $this->db->insert('healing_actions', [
                    'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'component' => $component,
                    'action' => 'notify', 'params' => json_encode($a->params),
                    'result' => 'sent', 'rule_applied' => $a->rule,
                ]);

                return ['ok' => true, 'notified' => true];
            default:
                return ['ok' => false, 'error' => 'unknown action'];
        }
    }
}
