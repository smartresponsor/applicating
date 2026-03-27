<?php

declare(strict_types=1);

namespace App\Component\Product\Traffic\Algo;

final class TokenBucket
{
    private float $tokens;
    private float $last;

    public function __construct(private readonly float $ratePerSec, private readonly float $burst)
    {
        $this->tokens = $burst;
        $this->last = microtime(true);
    }

    public function allow(int $cost = 1): bool
    {
        $now = microtime(true);
        $elapsed = $now - $this->last;
        $this->last = $now;
        $this->tokens = min($this->burst, $this->tokens + $elapsed * $this->ratePerSec);
        if ($this->tokens >= $cost) {
            $this->tokens -= $cost;

            return true;
        }

        return false;
    }
}
