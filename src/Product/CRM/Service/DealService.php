<?php

declare(strict_types=1);

namespace App\Product\CRM\Service;

use Doctrine\DBAL\Connection;

final class DealService
{
    public function __construct(private Connection $db)
    {
    }

    public function create(array $data): int
    {
        // Требуемые поля: title
        if (!isset($data['title']) || '' === trim((string) $data['title'])) {
            throw new \InvalidArgumentException('title required');
        }

        // Пайплайн/стадия
        $pipelineId = isset($data['pipeline_id']) ? (int) $data['pipeline_id'] : 0;
        if ($pipelineId <= 0) {
            // берём дефолтный пайплайн
            $row = $this->db->fetchAssociative(
                'SELECT id FROM crm_pipeline WHERE tenant_id IS NOT DISTINCT FROM :t AND product_id IS NOT DISTINCT FROM :p AND is_default=TRUE',
                ['t' => $data['tenant_id'] ?? null, 'p' => $data['product_id'] ?? null]
            );
            if (!$row) {
                throw new \RuntimeException('default pipeline not found (create it first)');
            }
            $pipelineId = (int) $row['id'];
        }
        $stageId = isset($data['stage_id']) ? (int) $data['stage_id'] : (int) $this->db->fetchOne(
            'SELECT id FROM crm_stage WHERE pipeline_id=:pid AND is_win=FALSE AND is_lost=FALSE ORDER BY position ASC LIMIT 1',
            ['pid' => $pipelineId]
        );

        $this->db->executeStatement(
            "INSERT INTO crm_deal(tenant_id, product_id, title, amount, currency, status, pipeline_id, stage_id, owner_user_id, customer_ref, created_at)
             VALUES(:t,:p,:title,:amount,:currency,'open',:pipeline,:stage,:owner,:customer,NOW())",
            [
                't' => $data['tenant_id'] ?? null,
                'p' => $data['product_id'] ?? null,
                'title' => (string) $data['title'],
                'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
                'currency' => isset($data['currency']) ? (string) $data['currency'] : null,
                'pipeline' => $pipelineId,
                'stage' => $stageId,
                'owner' => isset($data['owner_user_id']) ? (int) $data['owner_user_id'] : null,
                'customer' => isset($data['customer_ref']) ? (string) $data['customer_ref'] : null,
            ]
        );
        $dealId = (int) $this->db->fetchOne("SELECT currval(pg_get_serial_sequence('crm_deal','id'))");

        $this->db->executeStatement(
            'INSERT INTO crm_deal_stage_history(deal_id, from_stage_id, to_stage_id, actor_user_id, moved_at)
             VALUES(:d,NULL,:to,:actor,NOW())',
            ['d' => $dealId, 'to' => $stageId, 'actor' => $data['actor_user_id'] ?? null]
        );

        return $dealId;
    }

    public function moveToStage(int $dealId, int $toStageId, ?int $actorUserId = null): void
    {
        $deal = $this->db->fetchAssociative('SELECT * FROM crm_deal WHERE id=:id', ['id' => $dealId]);
        if (!$deal) {
            throw new \RuntimeException('deal not found');
        }

        $stage = $this->db->fetchAssociative('SELECT * FROM crm_stage WHERE id=:id', ['id' => $toStageId]);
        if (!$stage) {
            throw new \RuntimeException('stage not found');
        }
        if ((int) $stage['pipeline_id'] !== (int) $deal['pipeline_id']) {
            throw new \RuntimeException('stage not in deal pipeline');
        }

        $this->db->beginTransaction();
        try {
            $this->db->executeStatement(
                'INSERT INTO crm_deal_stage_history(deal_id, from_stage_id, to_stage_id, actor_user_id, moved_at)
                 VALUES(:d,:from,:to,:actor,NOW())',
                ['d' => $dealId, 'from' => $deal['stage_id'], 'to' => $toStageId, 'actor' => $actorUserId]
            );

            $status = (string) $deal['status'];
            $closeAt = null;
            $lostReason = null;
            if ($stage['is_win']) {
                $status = 'won';
                $closeAt = 'NOW()';
            } elseif ($stage['is_lost']) {
                $status = 'lost';
                $closeAt = 'NOW()';
            }

            if ('NOW()' === $closeAt) {
                $this->db->executeStatement(
                    'UPDATE crm_deal SET stage_id=:to, status=:st, updated_at=NOW(), close_at=NOW() WHERE id=:d',
                    ['to' => $toStageId, 'st' => $status, 'd' => $dealId]
                );
            } else {
                $this->db->executeStatement(
                    'UPDATE crm_deal SET stage_id=:to, status=:st, updated_at=NOW() WHERE id=:d',
                    ['to' => $toStageId, 'st' => $status, 'd' => $dealId]
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function list(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (array_key_exists('tenant_id', $filters)) {
            $where[] = 'tenant_id IS NOT DISTINCT FROM :t';
            $params['t'] = $filters['tenant_id'];
        }
        if (array_key_exists('product_id', $filters)) {
            $where[] = 'product_id IS NOT DISTINCT FROM :p';
            $params['p'] = $filters['product_id'];
        }
        if (array_key_exists('status', $filters) && $filters['status']) {
            $where[] = 'status = :s';
            $params['s'] = $filters['status'];
        }
        $sql = 'SELECT * FROM crm_deal'.(count($where) ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY created_at DESC LIMIT 200';

        return $this->db->fetchAllAssociative($sql, $params);
    }
}
