<?php
declare(strict_types=1);

namespace App\Component\Product\Catalog\Product;

use Doctrine\DBAL\Connection;

final class CatalogFacetService
{
    public function __construct(private readonly Connection $db) {}

    /** @param array<string,mixed> $filters */
    public function search(array $filters, int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock, product_categories, product_attrs FROM product_read WHERE 1=1';
        $params = [];
        $types = [];

        if (isset($filters['status'])) { $sql .= ' AND product_status = :status'; $params['status'] = $filters['status']; }
        if (isset($filters['min_price'])) { $sql .= ' AND product_price_amount >= :min_price'; $params['min_price'] = (int)$filters['min_price']; $types['min_price'] = \PDO::PARAM_INT; }
        if (isset($filters['max_price'])) { $sql .= ' AND product_price_amount <= :max_price'; $params['max_price'] = (int)$filters['max_price']; $types['max_price'] = \PDO::PARAM_INT; }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $sql .= ' AND product_categories && :catids';
            $params['catids'] = '{' . implode(',', array_map(fn($x)=>$x, $filters['category_ids'])) . '}';
        }
        if (!empty($filters['attrs']) && is_array($filters['attrs'])) {
            foreach ($filters['attrs'] as $k => $v) {
                $param = 'attr_' . preg_replace('/[^a-z0-9_]/i', '_', (string)$k);
                $sql .= " AND product_attrs ->> :$param = :{$param}_v";
                $params[$param] = (string)$k;
                $params["{$param}_v"] = (string)$v;
            }
        }
        if (isset($filters['q'])) { $sql .= ' AND product_title ILIKE :q'; $params['q'] = '%' . $filters['q'] . '%'; }

        $sql .= ' ORDER BY updated_at DESC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit; $types['limit'] = \PDO::PARAM_INT;
        $params['offset'] = $offset; $types['offset'] = \PDO::PARAM_INT;

        return $this->db->executeQuery($sql, $params, $types)->fetchAllAssociative();
    }

    /** @param array<string,mixed> $filters */
    public function facetStats(array $filters): array
    {
        $base = 'FROM product_read WHERE 1=1';
        $params = [];
        if (isset($filters['status'])) { $base .= ' AND product_status = :status'; $params['status'] = $filters['status']; }
        if (isset($filters['q'])) { $base .= ' AND product_title ILIKE :q'; $params['q'] = '%' . $filters['q'] . '%'; }

        $byStatus = $this->db->executeQuery("SELECT product_status, COUNT(*) cnt $base GROUP BY product_status", $params)->fetchAllAssociative();
        $byPrice = $this->db->executeQuery("SELECT width_bucket(product_price_amount, 0, 100000, 10) bucket, COUNT(*) cnt $base GROUP BY bucket ORDER BY bucket", $params)->fetchAllAssociative();

        return ['status' => $byStatus, 'priceBuckets' => $byPrice];
    }
}
