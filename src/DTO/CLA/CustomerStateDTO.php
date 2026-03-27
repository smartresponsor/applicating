<?php

declare(strict_types=1);

namespace App\Component\Product\DTO\CLA;

final class CustomerStateDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $customerId,
        public readonly int $daysSinceSignup,
        public readonly int $daysSinceLastOrder,
        public readonly int $ordersCount,
        public readonly float $avgOrderValue,
        public readonly int $supportTickets,
        public readonly ?string $email = null,
        public readonly ?string $webhook = null,
        public readonly string $locale = 'en',
    ) {
    }
}
