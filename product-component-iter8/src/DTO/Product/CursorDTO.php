<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

final class CursorDTO
{
    public function __construct(
        public readonly ?string $cursor = null,
        public readonly ?int $limit = 20
    ) {}
}
