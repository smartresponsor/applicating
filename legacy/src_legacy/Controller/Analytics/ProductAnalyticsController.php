<?php

declare(strict_types=1);

namespace App\Controller\Analytics;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProductAnalyticsController
{
    public function __construct(private Connection $db)
    {
    }

    #[Route('/api/analytics/product/summary', methods: ['GET'])]
    public function summary(Request $req): JsonResponse
    {
        $from = (string) $req->query->get('from');
        $to = (string) $req->query->get('to');
        $group = (string) ($req->query->get('group') ?? 'tenant');
        if (!$from || !$to) {
            return new JsonResponse(['error' => 'from & to required (YYYY-MM-DD)'], 400);
        } $groupExpr = 'product' === $group ? 'product_id' : ('day' === $group ? 'day' : 'tenant_id');
        $rows = $this->db->fetchAllAssociative('SELECT ' + $groupExpr + ' as grp, SUM(revenue) as revenue, SUM(usage_units) as usage_units, SUM(payments_succeeded) as payments_ok, SUM(payments_failed) as payments_fail, SUM(refunds) as refunds, AVG(trust_score) as trust_avg FROM product_analytics_daily WHERE day BETWEEN ? AND ? GROUP BY ' + $groupExpr + ' ORDER BY ' + $groupExpr + ' ASC', [$from, $to]);

        return new JsonResponse(['group' => $group, 'from' => $from, 'to' => $to, 'rows' => $rows], 200);
    }

    #[Route('/api/analytics/product/summary.csv', methods: ['GET'])]
    public function summaryCsv(Request $req): Response
    {
        $res = $this->summary($req);
        $data = json_decode($res->getContent(), true);
        $csv = "group,revenue,usage_units,payments_ok,payments_fail,refunds,trust_avg\n";
        foreach ($data['rows'] as $r) {
            $csv .= sprintf("%s,%.2f,%.6f,%d,%d,%d,%.2f\n", $r['grp'], $r['revenue'] ?? 0, $r['usage_units'] ?? 0, $r['payments_ok'] ?? 0, $r['payments_fail'] ?? 0, $r['refunds'] ?? 0, $r['trust_avg'] ?? 0);
        }

return new Response($csv, 200, ['Content-Type' => 'text/csv']);
    }

    #[Route('/api/analytics/product/cohort', methods: ['GET'])]
    public function cohort(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT first_payment_month, COUNT(DISTINCT tenant_id) AS tenants FROM vw_first_payment_month GROUP BY first_payment_month ORDER BY first_payment_month ASC');

        return new JsonResponse(['rows' => $rows], 200);
    }
}
