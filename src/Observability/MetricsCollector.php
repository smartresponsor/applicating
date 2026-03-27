<?php

declare(strict_types=1);

namespace App\Component\Product\Observability;

use App\Component\Product\Observability\DTO\MetricPoint;
use Doctrine\DBAL\Connection;

final class MetricsCollector
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function write(MetricPoint $p): void
    {
        $this->db->insert('metrics_timeseries', [
            'ts' => $p->ts,
            'name' => $p->name,
            'labels' => json_encode($p->labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'value' => $p->value,
        ]);
    }

    /** @return array<int,array<string,mixed>> */
    public function read(string $name, string $fromIso, string $toIso): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT ts, value, labels FROM metrics_timeseries WHERE name=? AND ts BETWEEN ? AND ? ORDER BY ts ASC',
            [$name, $fromIso, $toIso]
        );
    }
}
