<?php

declare(strict_types=1);

namespace App\Component\Product\LLM\Builtin;

use App\Component\Product\LLM\PluginInterface;

final class AdvisorSLA implements PluginInterface
{
    public function metadata(): array
    {
        return ['name' => 'AdvisorSLA', 'version' => '1.0', 'domain' => 'sla'];
    }

    public function analyze(array $context): array
    {
        $breaches = (int) ($context['sla']['breaches'] ?? 0);
        if ($breaches > 5) {
            return [['action' => 'issue_credits', 'params' => ['cap_cents' => 5000], 'score' => 0.8, 'rationale' => 'many breaches']];
        }

        return [];
    }
}
