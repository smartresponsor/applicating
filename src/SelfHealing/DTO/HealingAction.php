<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing\DTO;

final class HealingAction
{
    /** @param array<string,mixed> $params */
    public function __construct(
        public string $rule,
        public string $action,   // retry|restart|rollback|disable|notify
        public array $params = [],
    ) {
    }
}
