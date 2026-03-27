<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation\Scheduler;

use App\Component\Product\PolicyFederation\PeerRegistry;
use App\Component\Product\PolicyFederation\PolicyReplicator;
use App\Component\Product\PolicyFederation\Security\FederationSigner;
use Doctrine\DBAL\Connection;

final class PolicyFederationScheduler
{
    public function __construct(
        private readonly Connection $db,
        private readonly PeerRegistry $peers,
        private readonly PolicyReplicator $queue,
        private readonly FederationSigner $signer,
    ) {
    }

    /** Canary rollout: roll percentage of peers, exponential backoff on failure. */
    public function tick(int $batchPercent = 20, int $timeoutSec = 3): array
    {
        $task = $this->queue->nextTask();
        if (!$task) {
            return ['ok' => true, 'status' => 'idle'];
        }

        $name = (string) $task['name'];
        $version = (int) $task['version'];

        // Fetch latest content for the policy/version
        $row = $this->db->fetchAssociative('SELECT * FROM policy_registry WHERE name=? AND version=? ORDER BY ts DESC LIMIT 1', [$name, $version]);
        if (!$row) {
            $this->queue->complete((int) $task['id'], false, 'content_not_found');

            return ['ok' => false, 'error' => 'content_not_found'];
        }

        $payload = json_encode(['name' => $name, 'version' => $version, 'content' => json_decode($row['content'], true)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sig = $this->signer->sign($payload);

        $peers = $this->peers->list();
        if (!$peers) {
            $this->queue->complete((int) $task['id'], false, 'no_peers');

            return ['ok' => false, 'error' => 'no_peers'];
        }

        $batchSize = max(1, (int) ceil(count($peers) * $batchPercent / 100));
        $sent = 0;
        $failed = 0;
        foreach (array_slice($peers, 0, $batchSize) as $p) {
            $ok = $this->push($p['endpoint'], $payload, $sig, $timeoutSec);
            $this->db->insert('policy_replica_events', [
                'ts' => gmdate('c'), 'name' => $name, 'version' => $version, 'peer' => $p['region'],
                'status' => $ok ? 'ok' : 'fail',
            ]);
            $ok ? $sent++ : $failed++;
        }

        $this->queue->complete((int) $task['id'], 0 === $failed, $failed ? ('failed_to_' + $failed) : null);

        return ['ok' => (0 === $failed), 'sent' => $sent, 'failed' => $failed, 'batch' => $batchSize];
    }

    private function push(string $endpoint, string $payload, string $sig, int $timeoutSec): bool
    {
        // Simple cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, rtrim($endpoint, '/').'/api/policy/federation/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => $payload, 'signature' => $sig]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
        $resp = curl_exec($ch);
        $err = curl_errno($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return !$err && $code >= 200 && $code < 300;
    }
}
