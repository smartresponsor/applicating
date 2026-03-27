<?php

declare(strict_types=1);

namespace App\Product\CRM\Service;

use Doctrine\DBAL\Connection;

final class ActivityService
{
    public function __construct(private Connection $db)
    {
    }

    public function add(int $dealId, string $type, array $content = [], ?string $dueAt = null, ?int $userId = null): int
    {
        $this->db->executeStatement(
            'INSERT INTO crm_activity(deal_id, type, content, due_at, created_by) VALUES(:d,:t,:c,CAST(:due AS TIMESTAMPTZ),:u)',
            ['d' => $dealId, 't' => $type, 'c' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'due' => $dueAt, 'u' => $userId]
        );

        return (int) $this->db->fetchOne("SELECT currval(pg_get_serial_sequence('crm_activity','id'))");
    }
}
