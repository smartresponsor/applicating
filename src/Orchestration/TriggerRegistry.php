<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration;

final class TriggerRegistry
{
    /**
     * event.type => [ {rule, action, params} ].
     *
     * @return array<string,array<int,array{rule:string, action:string, params:array<string,mixed>}>>
     */
    public function rules(): array
    {
        return [
            'abi.forecast.ready' => [[
                'rule' => 'forecast_uptrend',
                'action' => 'pricing.adjust',
                'params' => ['direction' => 'up', 'max_pct' => 0.05],
            ]],
            'cla.customer.at_risk' => [[
                'rule' => 'high_churn',
                'action' => 'campaign.winback',
                'params' => ['discount_pct' => 10],
            ]],
            'sla.breach' => [[
                'rule' => 'sla_gap',
                'action' => 'credit.issue',
                'params' => ['cap_cents' => 5000],
            ]],
        ];
    }
}
