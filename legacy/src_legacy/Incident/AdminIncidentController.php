<?php

declare(strict_types=1);

namespace App\Component\Product\Incident;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AdminIncidentController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function list(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT id, alertname, summary FROM ai_incidents ORDER BY id DESC LIMIT 100') ?: [];

        return new JsonResponse($rows);
    }
}
