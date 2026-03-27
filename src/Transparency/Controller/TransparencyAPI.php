<?php

declare(strict_types=1);

namespace App\Component\Product\Transparency\Controller;

use App\Component\Product\Transparency\MetricsAggregator;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TransparencyAPI
{
    public function __construct(
        private readonly MetricsAggregator $metrics,
        private readonly Connection $db,
    ) {
    }

    #[Route('/api/public/metrics', methods: ['GET'])]
    public function metrics(Request $req): JsonResponse
    {
        $win = (int) $req->get('window', 60);

        return new JsonResponse(['ok' => true, 'metrics' => $this->metrics->aggregate($win)]);
    }

    #[Route('/api/public/audit', methods: ['GET'])]
    public function audit(Request $req): JsonResponse
    {
        $limit = (int) $req->get('limit', 50);
        $rows = $this->db->fetchAllAssociative('SELECT ts, type, hash FROM system_audit_ledger ORDER BY id DESC LIMIT ?', [$limit]);

        return new JsonResponse(['ok' => true, 'audit' => $rows]);
    }

    #[Route('/api/public/export', methods: ['GET'])]
    public function export(Request $req): JsonResponse
    {
        $from = (string) $req->get('from', '');
        $to = (string) $req->get('to', '');
        $rows = $this->db->fetchAllAssociative("
            SELECT ts, source, event, hash 
            FROM system_audit_log 
            WHERE ($1='' OR ts>=$1) AND ($2='' OR ts<=$2)
            ORDER BY ts ASC
        ", [$from, $to]);

        return new JsonResponse(['ok' => true, 'log' => $rows]);
    }
}
