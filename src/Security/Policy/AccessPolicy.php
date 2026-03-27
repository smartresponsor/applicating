<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Policy;

final class AccessPolicy
{
    public function __construct(
        public string $name,
        /** @var array<string,mixed> */
        public array $rules = [],
    ) {
    }
}
