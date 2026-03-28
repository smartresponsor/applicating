<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Policy;

final class BusinessPolicyEngine
{
    /**
     * Простейшие правила бизнес-решений на основе прогноза.
     *
     * @param array{avg:float,slope:float,predicted:float} $f
     * @param array{margin:float,tenant_priority:string}   $ctx
     *
     * @return array{decision:string,params:array<string,mixed>}
     */
    public function decide(array $f, array $ctx): array
    {
        $decision = 'no-change';
        $params = [];
        // Рост и высокая маржинальность → повысить цену на 3-7% (в зависимости от приоритета)
        if ($f['slope'] > 0 && $f['predicted'] > $f['avg'] && $ctx['margin'] >= 0.3) {
            $pct = 'gold' === $ctx['tenant_priority'] ? 0.03 : 0.05;
            $decision = 'price_adjust_up';
            $params = ['percent' => $pct];
        }
        // Падение и низкая маржинальность → повысить SLA, удержать клиента
        if ($f['slope'] < 0 && $f['predicted'] < $f['avg'] * 0.9) {
            $decision = 'sla_raise';
            $params = ['target_uptime' => '99.9'];
        }
        // Стабильно низкий прогноз → промо/кредитный лимит
        if ($f['predicted'] < $f['avg'] * 0.8) {
            $decision = 'promo_credit';
            $params = ['credit_cents' => 2000];
        }

        return ['decision' => $decision, 'params' => $params];
    }
}
