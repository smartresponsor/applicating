<?php

declare(strict_types=1);

namespace App\Component\Product\Entity\ABI;

final class ABIAudit
{
    public function __construct(
        public int $id,
        public string $ts,
        public string $tenant_id,
        public float $revenue_forecast,
        public string $action,
        public string $outcome,
        public string $details,
    ) {
    }
}
