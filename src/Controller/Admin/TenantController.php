<?php

declare(strict_types=1);

namespace App\Component\Product\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

final class TenantController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function list(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT id, name, status, plan FROM tenants ORDER BY id') ?: [];

        return new JsonResponse($rows);
    }
}
