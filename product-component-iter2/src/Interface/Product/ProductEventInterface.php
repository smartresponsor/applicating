<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

interface ProductEventInterface
{
    public function eventName(): string;
    /** @return array<string, mixed> */
    public function payload(): array;
    public function occurredAt(): \DateTimeImmutable;
}
