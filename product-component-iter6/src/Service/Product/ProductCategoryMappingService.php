<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use Doctrine\DBAL\Connection;

final class ProductCategoryMappingService
{
    public function __construct(private readonly Connection $db) {}

    public function assign(string $productId, string $categoryId): void
    {
        $this->db->executeStatement(
            'INSERT INTO product_category_map(product_id, category_id) VALUES(:p,:c) ON CONFLICT DO NOTHING',
            ['p' => $productId, 'c' => $categoryId]
        );
    }

    public function unassign(string $productId, string $categoryId): void
    {
        $this->db->executeStatement(
            'DELETE FROM product_category_map WHERE product_id = :p AND category_id = :c',
            ['p' => $productId, 'c' => $categoryId]
        );
    }
}
