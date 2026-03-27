<?php

declare(strict_types=1);

namespace App\Component\Product\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

final class BillingController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function invoices(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT id, tenant_id, period_start, period_end, subtotal_cents, tax_cents, total_cents, currency FROM invoices ORDER BY id DESC LIMIT 200') ?: [];

        return new JsonResponse($rows);
    }
}
