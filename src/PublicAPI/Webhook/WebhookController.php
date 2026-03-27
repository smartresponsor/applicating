<?php

declare(strict_types=1);

namespace App\Component\Product\PublicAPI\Webhook;

use App\Component\Product\PublicAPI\Security\HmacSigner;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class WebhookController
{
    public function __construct(private readonly Connection $db)
    {
    }

    #[Route('/api/public/v1/webhooks/ingest', methods: ['POST'])]
    public function ingest(Request $req): JsonResponse
    {
        $payload = $req->getContent() ?: '';
        $sig = $req->headers->get('X-Signature') ?? '';
        $secret = $_ENV['WEBHOOK_SECRET'] ?? 'dev_secret';
        if (!HmacSigner::verify($secret, $payload, $sig)) {
            return new JsonResponse(['ok' => false, 'error' => 'bad signature'], 401);
        }
        $data = json_decode($payload, true) ?: [];
        $this->db->insert('webhook_events', [
            'ts' => gmdate('c'),
            'source' => $data['source'] ?? 'unknown',
            'type' => $data['type'] ?? 'unknown',
            'payload' => $payload,
            'delivery_status' => 'queued',
        ]);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/public/v1/webhooks/deliver', methods: ['POST'])]
    public function deliver(Request $req): JsonResponse
    {
        $id = (int) ($req->get('id') ?? 0);
        $row = $this->db->fetchAssociative('SELECT * FROM webhook_events WHERE id=?', [$id]);
        if (!$row) {
            return new JsonResponse(['ok' => false, 'error' => 'not found'], 404);
        }
        $this->db->update('webhook_events', ['delivery_status' => 'delivered', 'last_error' => null], ['id' => $id]);

        return new JsonResponse(['ok' => true, 'delivered' => true]);
    }
}
