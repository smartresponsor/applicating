<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant\Provision;

use Doctrine\DBAL\Connection;

final class TenantProvisioner
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function createTenant(string $id, string $name, string $mode = 'rls'): void
    {
        $this->db->insert('tenants', ['id' => $id, 'name' => $name, 'status' => 'active', 'plan' => 'free']);
        if ('schema' === $mode) {
            $this->db->executeStatement('SELECT ensure_tenant_schema(:t)', ['t' => $id]);
        }
    }

    /** @param array<int,array{sku:string,title:string,price:int}> $items */
    public function seedProducts(string $tenant, array $items, string $mode = 'rls'): int
    {
        $ins = 0;
        foreach ($items as $i) {
            if ('schema' === $mode) {
                $sql = 'INSERT INTO '.$this->db->quoteIdentifier($tenant).'.product (product_sku, product_title_v2, product_slug, product_brand, product_price_amount, product_price_currency, product_status, product_stock) VALUES (?,?,?,?,?,?,?,?) ON CONFLICT (product_sku) DO NOTHING';
                $this->db->executeStatement($sql, [$i['sku'], $i['title'], strtolower(str_replace(' ', '-', $i['title'])), 'Demo', $i['price'], 'USD', 'active', 100]);
            } else {
                $sql = 'INSERT INTO public.product (tenant_id, product_sku, product_title_v2, product_slug, product_brand, product_price_amount, product_price_currency, product_status, product_stock) VALUES (?,?,?,?,?,?,?,?,?) ON CONFLICT (product_sku) DO NOTHING';
                $this->db->executeStatement($sql, [$tenant, $i['sku'], $i['title'], strtolower(str_replace(' ', '-', $i['title'])), 'Demo', $i['price'], 'USD', 'active', 100]);
            }
            ++$ins;
        }

        return $ins;
    }
}
