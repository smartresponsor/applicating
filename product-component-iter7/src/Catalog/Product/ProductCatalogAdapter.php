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
        $filters = $this->filtersFrom($query);
        $sort = in_array($query['sort'] ?? 'updated_desc', ['price_asc','price_desc','stock_desc','stock_asc','updated_desc','updated_asc'], true)
            ? (string)$query['sort'] : 'updated_desc';
        $cursor = isset($query['cursor']) ? (string)$query['cursor'] : null;
        $limit = isset($query['limit']) ? max(1, min((int)$query['limit'], 100)) : 20;

        $orderBy = match($sort) {
            'price_asc'  => 'product_price_amount ASC, id ASC',
            'price_desc' => 'product_price_amount DESC, id ASC',
            'stock_asc'  => 'product_stock ASC, id ASC',
            'stock_desc' => 'product_stock DESC, id ASC',
            'updated_asc'=> 'updated_at ASC, id ASC',
            default      => 'updated_at DESC, id ASC',
        };

        $sql = 'SELECT id, product_sku, product_title, product_price_amount, product_price_currency, product_status, product_stock, product_categories, product_attrs, updated_at FROM product_read WHERE 1=1';
        $params = [];
        $types = [];

        $this->applyFilters($filters, $sql, $params, $types);

        if ($cursor) {
            // курсор — это base64("updated_at|id")
            $decoded = base64_decode($cursor, true);
            if ($decoded) {
                [$updated, $id] = explode('|', $decoded, 2);
                if (str_ends_with($orderBy, 'DESC, id ASC') || $sort === 'updated_desc' or $sort === 'price_desc' or $sort === 'stock_desc') {
                    $sql .= ' AND (updated_at, id) < (:updated, :id)';
                } else {
                    $sql .= ' AND (updated_at, id) > (:updated, :id)';
                }
                $params['updated'] = $updated;
                $params['id'] = $id;
            }
        }

        $sql .= ' ORDER BY ' . $orderBy . ' LIMIT :limit';
        $params['limit'] = $limit; $types['limit'] = \PDO::PARAM_INT;

        $rows = $this->db->executeQuery($sql, $params, $types)->fetchAllAssociative();

        $nextCursor = null;
        if (count($rows) === $limit) {
            $last = end($rows);
            $nextCursor = base64_encode($last['updated_at'] . '|' . $last['id']);
        }

        return [
            'items' => $rows,
            'next_cursor' => $nextCursor,
            'sort' => $sort,
            'limit' => $limit,
            'filters' => $filters,
            'facets' => $this->facetStats($filters),
        ];
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
    private function applyFilters(array $filters, string &$sql, array &$params, array &$types): void
    {
        if (!empty($filters['status'])) { $sql .= ' AND product_status = :status'; $params['status'] = $filters['status']; }
        if (isset($filters['min_price'])) { $sql .= ' AND product_price_amount >= :min_price'; $params['min_price'] = (int)$filters['min_price']; $types['min_price'] = \PDO::PARAM_INT; }
        if (isset($filters['max_price'])) { $sql .= ' AND product_price_amount <= :max_price'; $params['max_price'] = (int)$filters['max_price']; $types['max_price'] = \PDO::PARAM_INT; }
        if (!empty($filters['q'])) { $sql .= ' AND product_title ILIKE :q'; $params['q'] = '%' . $filters['q'] . '%'; }
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $sql .= ' AND product_categories && :catids';
            $params['catids'] = '{' . implode(',', array_map(fn($x)=>$x, $filters['category_ids'])) . '}';
        }
        if (!empty($filters['attrs']) && is_array($filters['attrs'])) {
            foreach ($filters['attrs'] as $k => $v) {
                $sql .= ' AND product_attrs ->> :attrkey = :attrval';
                $params['attrkey'] = (string)$k;
                $params['attrval'] = (string)$v;
            }
        }
    }

    /** @param array<string,mixed> $query */
    private function filtersFrom(array $query): array
    {
        return [
            'q' => $query['q'] ?? null,
            'status' => $query['status'] ?? null,
            'min_price' => isset($query['min_price']) ? (int)$query['min_price'] : null,
            'max_price' => isset($query['max_price']) ? (int)$query['max_price'] : null,
            'category_ids' => (isset($query['category_ids']) && is_array($query['category_ids'])) ? $query['category_ids'] : null,
            'attrs' => (isset($query['attrs']) && is_array($query['attrs'])) ? $query['attrs'] : null,
        ];
    }

    /** @param array<string,mixed> $filters */
    public function facetStats(array $filters): array
    {
        $base = 'FROM product_read WHERE 1=1';
        $params = [];
        if (!empty($filters['status'])) { $base .= ' AND product_status = :status'; $params['status'] = $filters['status']; }
        if (!empty($filters['q'])) { $base .= ' AND product_title ILIKE :q'; $params['q'] = '%' . $filters['q'] . '%'; }

        $byStatus = $this->db->executeQuery("SELECT product_status, COUNT(*) cnt $base GROUP BY product_status", $params)->fetchAllAssociative();
        $byPrice = $this->db->executeQuery("SELECT width_bucket(product_price_amount, 0, 100000, 10) bucket, COUNT(*) cnt $base GROUP BY bucket ORDER BY bucket", $params)->fetchAllAssociative();
        $byCategory = $this->db->executeQuery("SELECT unnest(product_categories) AS cat, COUNT(*) cnt $base GROUP BY cat ORDER BY cnt DESC LIMIT 50", $params)->fetchAllAssociative();

        return ['status' => $byStatus, 'priceBuckets' => $byPrice, 'categories' => $byCategory];
    }
}
