<?php

declare(strict_types=1);

namespace App\Component\Product\Entity\CLA;

final class CustomerLifecycleEvent
{
    public function __construct(
        public int $id,
        public string $ts,
        public string $tenant_id,
        public string $customer_id,
        public string $stage,
        public float $churn_score,
        public string $campaign,
        public string $channel,
        public string $details,
    ) {
    }
}
