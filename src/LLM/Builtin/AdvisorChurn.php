<?php

declare(strict_types=1);

namespace App\Component\Product\LLM\Builtin;

use App\Component\Product\LLM\PluginInterface;

final class AdvisorChurn implements PluginInterface
{
    public function metadata(): array
    {
        return ['name' => 'AdvisorChurn', 'version' => '1.0', 'domain' => 'cla'];
    }

    public function analyze(array $context): array
    {
        $churn = (float) ($context['cla']['churn_avg'] ?? 0);
        if ($churn >= 0.7) {
            return [['action' => 'run_winback', 'params' => ['discount_pct' => 10], 'score' => 0.88, 'rationale' => 'high churn']];
        }

        return [];
    }
}
