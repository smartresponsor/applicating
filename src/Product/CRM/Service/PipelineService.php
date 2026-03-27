<?php

declare(strict_types=1);

namespace App\Product\CRM\Service;

use Doctrine\DBAL\Connection;

final class PipelineService
{
    public function __construct(private Connection $db)
    {
    }

    public function ensureDefault(?string $tenantId = null, ?int $productId = null): array
    {
        $row = $this->db->fetchAssociative(
            'SELECT * FROM crm_pipeline WHERE tenant_id IS NOT DISTINCT FROM :t AND product_id IS NOT DISTINCT FROM :p AND is_default = TRUE',
            ['t' => $tenantId, 'p' => $productId]
        );
        if ($row) {
            return $row;
        }

        $this->db->executeStatement(
            'INSERT INTO crm_pipeline(tenant_id, product_id, name, is_default) VALUES(:t,:p,:n,TRUE)',
            ['t' => $tenantId, 'p' => $productId, 'n' => 'Default Pipeline']
        );
        $pipelineId = (int) $this->db->fetchOne("SELECT currval(pg_get_serial_sequence('crm_pipeline','id'))");

        // Стандартные стадии
        $stages = [
            ['name' => 'New',       'is_win' => false, 'is_lost' => false],
            ['name' => 'Qualified', 'is_win' => false, 'is_lost' => false],
            ['name' => 'Proposal',  'is_win' => false, 'is_lost' => false],
            ['name' => 'Won',       'is_win' => true,  'is_lost' => false],
            ['name' => 'Lost',      'is_win' => false, 'is_lost' => true],
        ];
        $pos = 1;
        foreach ($stages as $s) {
            $this->db->executeStatement(
                'INSERT INTO crm_stage(pipeline_id, name, position, is_win, is_lost) VALUES(:pid,:n,:pos,:w,:l)',
                ['pid' => $pipelineId, 'n' => $s['name'], 'pos' => $pos++, 'w' => $s['is_win'], 'l' => $s['is_lost']]
            );
        }

        return $this->db->fetchAssociative('SELECT * FROM crm_pipeline WHERE id=:id', ['id' => $pipelineId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function stages(int $pipelineId): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM crm_stage WHERE pipeline_id=:id ORDER BY position ASC', ['id' => $pipelineId]);
    }
}
