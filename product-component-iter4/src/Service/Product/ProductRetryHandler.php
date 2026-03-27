<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

final class ProductRetryHandler
{
    public function nextDelayMs(int $attempt): int
    {
        // экспоненциальный backoff с лимитом ~60s
        $base = 100; // 100ms
        $delay = (int)($base * (2 ** max(0, $attempt - 1)));
        return min($delay, 60000);
    }
}
