<?php

declare(strict_types=1);

namespace App\Component\Product\AI\SelfHealing;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SelfHealingController
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function webhook(Request $r): JsonResponse
    {
        $payload = json_decode($r->getContent() ?: '{}', true);
        $alerts = $payload['alerts'] ?? [];
        foreach ($alerts as $a) {
            $action = $this->decideAction($a);
            $this->db->insert('healing_events', [
                'alertname' => $a['labels']['alertname'] ?? '',
                'summary' => $a['annotations']['summary'] ?? '',
                'action' => $action,
                'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        }

        return new JsonResponse(['ok' => true]);
    }

    private function decideAction(array $a): string
    {
        $alert = strtolower($a['labels']['alertname'] ?? '');

        return match (true) {
            str_contains($alert, 'cpu') => 'scale-up',
            str_contains($alert, 'oom') => 'restart',
            str_contains($alert, 'pod') => 'restart',
            default => 'noop',
        };
    }
}
