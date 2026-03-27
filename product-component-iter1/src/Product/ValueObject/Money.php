<?php
declare(strict_types=1);

namespace App\Component\Product\ValueObject;

final class Money
{
    public function __construct(
        private int $amount, // minor units (cents)
        private string $currency = 'USD'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Money amount must be >= 0');
        }
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new \InvalidArgumentException('Invalid currency code');
        }
    }

    public function amount(): int { return $this->amount; }
    public function currency(): string { return $this->currency; }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
