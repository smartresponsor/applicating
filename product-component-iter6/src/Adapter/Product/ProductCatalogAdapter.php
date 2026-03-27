<?php
declare(strict_types=1);

namespace App\Component\Product\Adapter\Product;

use App\Component\Product\Catalog\Product\CatalogFacetService;

final class ProductCatalogAdapter
{
    public function __construct(private readonly CatalogFacetService $facets) {}

    /** @param array<string,mixed> $query */
    public function list(array $query): array
    {
        $filters = [
            'q' => $query['q'] ?? null,
            'status' => $query['status'] ?? null,
            'min_price' => isset($query['min_price']) ? (int)$query['min_price'] : null,
            'max_price' => isset($query['max_price']) ? (int)$query['max_price'] : null,
            'category_ids' => isset($query['category_ids']) && is_array($query['category_ids']) ? $query['category_ids'] : null,
            'attrs' => isset($query['attrs']) && is_array($query['attrs']) ? $query['attrs'] : null,
        ];
        $limit = isset($query['limit']) ? (int)$query['limit'] : 20;
        $offset = isset($query['offset']) ? (int)$query['offset'] : 0;

        return [
            'items' => $this->facets->search($filters, $limit, $offset),
            'facets' => $this->facets->facetStats($filters),
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}
