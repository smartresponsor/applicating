<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\Retention;

use Doctrine\DBAL\Connection;

final class DataRetentionManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function schedule(string $dataset, int $days): void
    {
        $this->db->insert('compliance_retention', [
            'ts' => gmdate('c'),
            'dataset' => $dataset,
            'ttl_days' => $days,
            'status' => 'scheduled',
        ]);
    }

    public function sweep(): int
    {
        // Заглушка: помечаем просроченные как deleted
        $count = (int) $this->db->fetchOne("SELECT COUNT(*) FROM compliance_retention WHERE NOW() - INTERVAL '1 day'*ttl_days > ts AND status='scheduled'") ?? 0;
        $this->db->executeStatement("UPDATE compliance_retention SET status='deleted', deleted_at=NOW() WHERE NOW() - INTERVAL '1 day'*ttl_days > ts AND status='scheduled'");

        return $count;
    }
}
