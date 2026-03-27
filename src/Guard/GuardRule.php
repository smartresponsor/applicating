<?php

declare(strict_types=1);

namespace App\Component\Product\Guard;

final class GuardRule
{
    public function __construct(
        public string $key,
        public string $description,
        public string $risk_level,           // low|medium|high
        public bool $requires_approval,
        /** @var callable(array<string,mixed>): bool */
        public $predicate,
    ) {
    }
}
