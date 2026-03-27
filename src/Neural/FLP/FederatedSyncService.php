<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\FLP;

use Doctrine\DBAL\Connection;

final class FederatedSyncService
{
    public function __construct(
        private readonly Connection $db,
        private readonly DiffCompressor $cmp,
        private readonly PrivacyLayer $privacy,
        private readonly SignatureService $sig,
    ) {
    }

    /** Собираем локальные веса за последние 24 часа, сжимаем, добавляем шум и подписываем. */
    public function collectAndSign(string $tenantId): array
    {
        $row = $this->db->fetchAssociative('SELECT weights FROM neural_local_weights WHERE tenant_id=? ORDER BY ts DESC LIMIT 1', [$tenantId]);
        $weights = $row ? json_decode((string) $row['weights'], true) : ['w' => 1.0];
        $pack = $this->cmp->compress($weights);
        $anonId = $this->privacy->anonymize($tenantId);
        $noised = $this->privacy->addNoise($pack, 0.01);
        $payload = ['anon_tenant' => $anonId, 'diff' => $noised, 'ts' => gmdate('c')];
        $sig = $this->sig->sign(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->db->insert('flp_sync_log', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'anon_tenant' => $anonId, 'payload' => json_encode($payload), 'signature' => $sig, 'direction' => 'out',
        ]);

        return ['payload' => $payload, 'signature' => $sig];
    }

    /** Принять пакет от другого узла, проверить подпись и применить агрегат к Fabric. */
    public function receive(string $payloadJson, string $signature): bool
    {
        if (!$this->sig->verify($payloadJson, $signature)) {
            return false;
        }
        $payload = json_decode($payloadJson, true) ?: [];
        $this->db->insert('flp_sync_log', [
            'ts' => gmdate('c'), 'tenant_id' => '*', 'anon_tenant' => ($payload['anon_tenant'] ?? '?'), 'payload' => $payloadJson, 'signature' => $signature, 'direction' => 'in',
        ]);
        // Наивное применение: записываем глобальный апдейт как среднее diff['w']
        $diff = $payload['diff'] ?? ['w' => 1.0];
        $adj = (float) ($diff['w'] ?? 1.0);
        $this->db->insert('neural_fabric_updates', ['ts' => gmdate('c'), 'global_adjust' => $adj]);

        return true;
    }
}
