<?php

declare(strict_types=1);

namespace App\Component\Product\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;

final class IncidentController
{
    public function list(): JsonResponse
    {
        return new JsonResponse([['id' => 101, 'alertname' => 'HighErrorRate', 'summary' => 'Hypothesis: bad deploy; Actions: rollback, check logs.']]);
    }
}
