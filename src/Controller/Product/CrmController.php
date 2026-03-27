<?php

declare(strict_types=1);

namespace App\Controller\Product;

use App\Product\CRM\Service\ActivityService;
use App\Product\CRM\Service\DealService;
use App\Product\CRM\Service\PipelineService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CrmController
{
    public function __construct(
        private Connection $db,
        private PipelineService $pipelines,
        private DealService $deals,
        private ActivityService $activities,
    ) {
    }

    #[Route('/api/product/crm/pipelines/default', methods: ['POST'])]
    public function ensureDefault(Request $r): JsonResponse
    {
        $t = $r->request->get('tenant_id');
        $p = $r->request->get('product_id');
        $row = $this->pipelines->ensureDefault($t ?: null, null !== $p ? (int) $p : null);
        $stages = $this->pipelines->stages((int) $row['id']);

        return new JsonResponse(['pipeline' => $row, 'stages' => $stages]);
    }

    #[Route('/api/product/crm/stages', methods: ['GET'])]
    public function stages(Request $r): JsonResponse
    {
        $pid = (int) $r->query->get('pipeline_id');
        if ($pid <= 0) {
            return new JsonResponse(['error' => 'pipeline_id required'], 400);
        }

        return new JsonResponse(['stages' => $this->pipelines->stages($pid)]);
    }

    #[Route('/api/product/crm/deals', methods: ['POST'])]
    public function createDeal(Request $r): JsonResponse
    {
        $data = $r->toArray(false);
        $id = $this->deals->create($data);

        return new JsonResponse(['id' => $id], 201);
    }

    #[Route('/api/product/crm/deals', methods: ['GET'])]
    public function listDeals(Request $r): JsonResponse
    {
        $filters = [
            'tenant_id' => $r->query->get('tenant_id'),
            'product_id' => $r->query->has('product_id') ? (int) $r->query->get('product_id') : null,
            'status' => $r->query->get('status'),
        ];

        return new JsonResponse(['rows' => $this->deals->list($filters)]);
    }

    #[Route('/api/product/crm/deals/{id}/move', methods: ['POST'])]
    public function moveDeal(int $id, Request $r): JsonResponse
    {
        $to = (int) $r->request->get('to_stage_id');
        $actor = null !== $r->request->get('actor_user_id') ? (int) $r->request->get('actor_user_id') : null;
        if ($to <= 0) {
            return new JsonResponse(['error' => 'to_stage_id required'], 400);
        }
        $this->deals->moveToStage($id, $to, $actor);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/product/crm/deals/{id}/activities', methods: ['POST'])]
    public function addActivity(int $id, Request $r): JsonResponse
    {
        $type = (string) $r->request->get('type', 'note');
        $content = $r->request->get('content');
        if (!is_array($content)) {
            $content = [];
        }
        $due = $r->request->get('due_at');
        $user = $r->request->get('user_id');
        $userId = null !== $user ? (int) $user : null;
        $actId = $this->activities->add($id, $type, $content, $due, $userId);

        return new JsonResponse(['id' => $actId], 201);
    }

    #[Route('/api/product/crm/funnel', methods: ['GET'])]
    public function funnel(): JsonResponse
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM vw_crm_funnel_30d');

        return new JsonResponse(['rows' => $rows]);
    }
}
