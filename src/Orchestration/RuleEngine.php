<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration;

final class RuleEngine
{
    /** @param array<string,mixed> $event @param array<string,mixed> $params */
    public function match(string $rule, array $event, array $params): bool
    {
        return match ($rule) {
            'forecast_uptrend' => (($event['payload']['slope'] ?? 0) > 0),
            'high_churn' => (($event['payload']['churn_score'] ?? 0) >= 0.7),
            'sla_gap' => (($event['payload']['gap_pct'] ?? 0) > 0),
            default => false,
        };
    }
}
