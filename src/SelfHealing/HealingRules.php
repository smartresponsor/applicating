<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing;

use App\Component\Product\SelfHealing\DTO\HealingAction;
use App\Component\Product\SelfHealing\DTO\IncidentReport;

final class HealingRules
{
    /** Подбирает действие по типу инцидента. */
    public function decide(IncidentReport $i): HealingAction
    {
        $key = $i->component;

        return match (true) {
            str_contains($key, 'error_rate') => new HealingAction('reduce_errors', 'restart', ['component' => 'api_worker']),
            str_contains($key, 'latency') => new HealingAction('latency_hotfix', 'rollback', ['service' => 'pricing', 'to_version' => 'stable']),
            str_contains($key, 'sla') => new HealingAction('sla_protect', 'notify', ['channel' => 'oncall']),
            default => new HealingAction('generic_retry', 'retry', ['retries' => 1, 'backoff_ms' => 5000]),
        };
    }
}
