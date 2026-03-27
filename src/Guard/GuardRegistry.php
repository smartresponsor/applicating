<?php

declare(strict_types=1);

namespace App\Component\Product\Guard;

final class GuardRegistry
{
    /** @var array<string,GuardRule> */
    private array $rules = [];

    public function register(GuardRule $rule): void
    {
        $this->rules[$rule->key] = $rule;
    }

    /** @param array<string,mixed> $params */
    public function check(string $action, array $params): array
    {
        $violations = [];
        foreach ($this->rules as $key => $rule) {
            $ok = ($rule->predicate)($params);
            if ($ok) {
                $violations[] = [
                    'rule' => $key,
                    'description' => $rule->description,
                    'risk_level' => $rule->risk_level,
                    'requires_approval' => $rule->requires_approval,
                ];
            }
        }
        // Вернуть максимальный риск, если есть
        $riskOrder = ['low' => 1, 'medium' => 2, 'high' => 3];
        $maxRisk = 'low';
        $req = false;
        foreach ($violations as $v) {
            if ($riskOrder[$v['risk_level']] > $riskOrder[$maxRisk]) {
                $maxRisk = $v['risk_level'];
            }
            if ($v['requires_approval']) {
                $req = true;
            }
        }

        return ['requires_approval' => $req, 'risk_level' => $maxRisk, 'violations' => $violations];
    }
}
