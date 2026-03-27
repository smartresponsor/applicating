<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Policy;

final class PolicyEngine
{
    /** @param array<string,mixed> $ctx */
    public function evaluate(AccessPolicy $policy, array $ctx): bool
    {
        // Простейшая ABAC-проверка: require role, tenant match, action in allow
        $roleOk = isset($ctx['role']) && in_array($ctx['role'], $policy->rules['roles'] ?? [], true);
        $tenantOk = !isset($policy->rules['tenant']) || ($ctx['tenant'] ?? null) === $policy->rules['tenant'];
        $actionOk = isset($ctx['action']) and in_array($ctx['action'], $policy->rules['allow'] ?? [], true);

        return $roleOk && $tenantOk && $actionOk;
    }
}
