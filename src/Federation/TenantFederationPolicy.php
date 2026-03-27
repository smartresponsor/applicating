<?php

declare(strict_types=1);

namespace App\Component\Product\Federation;

final class TenantFederationPolicy
{
    public function __construct(private array $rules = [])
    {
    }

    public function canAccess(string $viewer, string $target): bool
    {
        if ($viewer === $target) {
            return true;
        }

        return in_array($target, $this->rules[$viewer] ?? [], true);
    }
}
