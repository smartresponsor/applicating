<?php
declare(strict_types=1);

namespace App\Component\Product\Catalog\Product;

use Doctrine\DBAL\Connection;

final class ProductCatalogService
{
    public function __construct(private readonly Connection $db) {}

    /** @param array<string,mixed> $filters */
    public function search(array $filters, int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock FROM product_read WHERE 1=1';
        $params = [];
        $types = [];

        if (isset($filters['status'])) { $sql .= ' AND product_status = :status'; $params['status'] = $filters['status']; }
        if (isset($filters['min_price'])) { $sql .= ' AND product_price_amount >= :min_price'; $params['min_price'] = (int)$filters['min_price']; $types['min_price'] = \PDO::PARAM_INT; }
        if (isset($filters['max_price'])) { $sql .= ' AND product_price_amount <= :max_price'; $params['max_price'] = (int)$filters['max_price']; $types['max_price'] = \PDO::PARAM_INT; }
        if (isset($filters['q'])) { $sql .= ' AND product_title ILIKE :q'; $params['q'] = '%' . $filters['q'] . '%'; }

        $sql .= ' ORDER BY updated_at DESC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit; $types['limit'] = \PDO::PARAM_INT;
        $params['offset'] = $offset; $types['offset'] = \PDO::PARAM_INT;

        return $this->db->executeQuery($sql, $params, $types)->fetchAllAssociative();
    }
}
