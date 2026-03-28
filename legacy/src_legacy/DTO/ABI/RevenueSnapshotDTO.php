<?php

declare(strict_types=1);

namespace App\Component\Product\DTO\ABI;

final class RevenueSnapshotDTO
{
    /** @param array<int,float> $monthlyRevenue */
    public function __construct(
        public readonly string $tenantId,
        public readonly array $monthlyRevenue,
        public readonly float $margin,
        public readonly string $tenantPriority = 'standard',
    ) {
    }
}
