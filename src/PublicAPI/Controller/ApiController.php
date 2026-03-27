<?php

declare(strict_types=1);

namespace App\Component\Product\PublicAPI\Controller;

use App\Component\Product\PublicAPI\RateLimit\WindowLimiter;
use App\Component\Product\PublicAPI\Security\ApiKeyAuth;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class ApiController
{
    public function __construct(
        private readonly Connection $db,
        private readonly ApiKeyAuth $auth,
        private readonly WindowLimiter $rl,
    ) {
    }

    private function guard(): ?JsonResponse
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? null;
        $row = $this->auth->validate($key);
        if (!$row) {
            return new JsonResponse(['error' => 'unauthorized'], 401);
        }
        if (!$this->rl->hit($row['api_key'])) {
            return new JsonResponse(['error' => 'rate_limited'], 429);
        }

        return null;
    }

    #[Route('/api/public/v1/abi/forecast', methods: ['GET'])]
    public function forecast(): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }
        $tenant = $_GET['tenant'] ?? 'tenantA';
        $avg = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(revenue_forecast),0) FROM abi_audit_events WHERE tenant_id = ?', [$tenant]) ?: 0);

        return new JsonResponse(['tenant' => $tenant, 'avg_revenue_forecast' => $avg]);
    }

    #[Route('/api/public/v1/cla/churn', methods: ['GET'])]
    public function churn(): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }
        $tenant = $_GET['tenant'] ?? 'tenantA';
        $avg = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(churn_score),0) FROM customer_lifecycle_events WHERE tenant_id = ?', [$tenant]) ?: 0);

        return new JsonResponse(['tenant' => $tenant, 'avg_churn_score' => $avg]);
    }

    #[Route('/api/public/v1/pricing/price', methods: ['GET'])]
    public function price(): JsonResponse
    {
        if ($g = $this->guard()) {
            return $g;
        }
        $tenant = $_GET['tenant'] ?? 'tenantA';
        $sku = $_GET['sku'] ?? 'SKU-001';
        $row = $this->db->fetchAssociative('SELECT base_price_cents FROM pricing WHERE tenant_id=? LIMIT 1', [$tenant]);
        $base = (int) ($row['base_price_cents'] ?? 1000);

        return new JsonResponse(['tenant' => $tenant, 'sku' => $sku, 'base_price_cents' => $base]);
    }
}
