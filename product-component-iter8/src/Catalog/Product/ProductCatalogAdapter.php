<?php
declare(strict_types=1);

namespace App\Component\Product\Catalog\Product;

use Doctrine\DBAL\Connection;

final class ProductCatalogAdapter
{
    public function __construct(private readonly Connection $db) {}

    /** @param array<string,mixed> $query */
    public function list(array $query): array
    {
        // В реальном проекте используем реализацию из итерации VII.
        $sql = 'SELECT id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock, updated_at FROM product_read ORDER BY updated_at DESC LIMIT 1';
        $items = $this->db->executeQuery($sql)->fetchAllAssociative();
        return ['items' => $items, 'next_cursor' => null, 'sort' => $query['sort'] ?? 'updated_desc', 'limit' => (int)($query['limit'] ?? 20), 'filters' => [], 'facets' => []];
    }

    /** @param array<string,mixed> $query */
    public function show(array $query): array
    {
        $id = (string)($query['id'] ?? '');
        if ($id === '') { throw new \InvalidArgumentException('id required'); }
        $row = $this->db->fetchAssociative('SELECT * FROM product_read WHERE id = :id', ['id' => $id]);
        if (!$row) { throw new \RuntimeException('Not found'); }
        return $row;
    }

    /** @param array<string,mixed> $filters */
    public function facetStats(array $filters): array
    {
        return ['status' => [], 'priceBuckets' => [], 'categories' => []];
    }
}
