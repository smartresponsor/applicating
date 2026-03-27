<?php
declare(strict_types=1);

namespace App\Component\Product\Controller\Product;

use App\Component\Product\Catalog\Product\ProductCatalogAdapter;

final class ProductCatalogController
{
    public function __construct(private readonly ProductCatalogAdapter $adapter) {}

    /** @param array<string,mixed> $query */
    public function list(array $query): array
    {
        return $this->adapter->list($query);
    }

    /** @param array<string,mixed> $query */
    public function show(array $query): array
    {
        return $this->adapter->show($query);
    }

    /** @param array<string,mixed> $query */
    public function filters(array $query): array
    {
        $filters = [
            'q' => $query['q'] ?? null,
            'status' => $query['status'] ?? null
        ];
        return ['facets' => $this->adapter->facetStats($filters)];
    }
}
