<?php
declare(strict_types=1);

namespace App\Component\Product\ValueObject;

final class Sku
{
    public function __construct(private string $value)
    {
        $v = trim($value);
        if ($v === '' || strlen($v) > 64) {
            throw new \InvalidArgumentException('Invalid SKU');
        }
        $this->value = strtoupper($v);
    }

    public function __toString(): string { return $this->value; }
    public function value(): string { return $this->value; }
}
