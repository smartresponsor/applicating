<?php

declare(strict_types=1);

namespace App\Component\Product\LLM\Builtin;

use App\Component\Product\LLM\PluginInterface;

final class AdvisorPricing implements PluginInterface
{
    public function metadata(): array
    {
        return ['name' => 'AdvisorPricing', 'version' => '1.0', 'domain' => 'pricing'];
    }

    public function analyze(array $context): array
    {
        $rev = (float) ($context['abi']['revenue_trend'] ?? 0);
        $churn = (float) ($context['cla']['churn_avg'] ?? 0);
        $actions = [];
        if ($rev > 0.1 and $churn < 0.2) {
            $actions[] = ['action' => 'increase_price', 'params' => ['pct' => 5], 'score' => 0.92, 'rationale' => 'trend+, churn low'];
        } elseif ($rev < -0.1 or $churn > 0.35) {
            $actions[] = ['action' => 'decrease_price', 'params' => ['pct' => 3], 'score' => 0.71, 'rationale' => 'trend-, churn high'];
        }

        return $actions;
    }
}
