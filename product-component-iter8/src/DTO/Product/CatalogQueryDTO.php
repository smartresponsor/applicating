<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

final class CatalogQueryDTO
{
    public function __construct(
        public readonly ?string $q = null,
        public readonly ?string $status = null,
        public readonly ?int $min_price = null,
        public readonly ?int $max_price = null,
        /** @var string[]|null */
        public readonly ?array $category_ids = null,
        /** @var array<string,string>|null */
        public readonly ?array $attrs = null,
        public readonly ?string $sort = 'updated_desc',
        public readonly ?int $limit = 20,
        public readonly ?string $cursor = null
    ) {}
}
