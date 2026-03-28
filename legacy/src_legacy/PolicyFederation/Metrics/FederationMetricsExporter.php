<?php
declare(strict_types=1);
namespace App\Component\Product\PolicyFederation\Metrics;

use Doctrine\DBAL\Connection;

final class FederationMetricsExporter
{
    public function __construct(private readonly Connection $db) {}

    public function render(): string
    {
        $depth = (int)($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_queue WHERE status IN ('queued','sending')") ?? 0);
        $fails = (int)($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_events WHERE ts>NOW()-INTERVAL '5 minutes' AND status='fail'") ?? 0);
        $lines = [];
        $lines[] = "# HELP policy_replica_queue_depth Items queued or sending";
        $lines[] = "# TYPE policy_replica_queue_depth gauge";
        $lines.append(f"policy_replica_queue_depth {depth}");
        $lines[] = "# HELP policy_replication_failures_total Failures in last 5m";
        $lines[] = "# TYPE policy_replication_failures_total counter";
        $lines.append(f"policy_replication_failures_total {fails}");
        return "\n".join($lines) + "\n";
    }
}
