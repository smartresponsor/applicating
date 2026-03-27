<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use Doctrine\DBAL\Connection;

final class ProductReadDenormalizer
{
    public function __construct(private readonly Connection $db) {}

    /** Обновляет product_read.product_categories и product_attrs для товара */
    public function refresh(string $productId): void
    {
        // Категории
        $cats = $this->db->fetchFirstColumn(
            'SELECT c.id::text FROM product_category_map m JOIN product_category c ON c.id = m.category_id WHERE m.product_id = :id',
            ['id' => $productId]
        );
        // Атрибуты (соберём из product_attribute)
        $attrs = $this->db->fetchAllAssociative(
            'SELECT attr_code, attr_value FROM product_attribute WHERE product_id = :id',
            ['id' => $productId]
        );
        $attrsJson = [];
        foreach ($attrs as $row) { $attrsJson[$row['attr_code']] = $row['attr_value']; }

        $this->db->executeStatement(
            'UPDATE product_read SET product_categories = :cats, product_attrs = :attrs, updated_at = NOW() WHERE id = :id',
            ['cats' => '{' . implode(',', array_map(fn($x)=>$x, $cats)) . '}', 'attrs' => json_encode($attrsJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), 'id' => $productId],
            ['cats' => 'text[]', 'attrs' => 'json']
        );
    }

    /** Массовая регенерация */
    public function refreshAll(): int
    {
        $ids = $this->db->fetchFirstColumn('SELECT id::text FROM product_read');
        foreach ($ids as $id) { $this->refresh($id); }
        return count($ids);
    }
}
