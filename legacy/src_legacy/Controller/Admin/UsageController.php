<?php
declare(strict_types=1);
namespace App\Component\Product\Controller\Admin;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;

final class UsageController
{
    public function __construct(private readonly Connection $db) {}

    public function current(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT tenant_id, SUM(requests) AS requests FROM monthly_usage WHERE month = date_trunc('month', CURRENT_DATE) GROUP BY tenant_id');
        // demo quota mapping
        $out = array_map(fn($r)=>['tenant_id'=>$r['tenant_id'],'requests'=>(int)$r['requests'],'quota'=>50000,'within'=>((int)$r['requests'])<50000], $rows);
        return new JsonResponse($out);
    }
}
