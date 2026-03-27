<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation\Controller;

use App\Component\Product\PolicyFederation\PeerRegistry;
use App\Component\Product\PolicyFederation\PolicyReplicator;
use App\Component\Product\PolicyFederation\Security\FederationSigner;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FederationAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly PeerRegistry $peers,
        private readonly PolicyReplicator $queue,
        private readonly FederationSigner $signer,
    ) {
    }

    #[Route('/api/policy/federation/peers', methods: ['GET', 'POST'])]
    public function peers(Request $req): JsonResponse
    {
        if ('POST' === $req->getMethod()) {
            $this->peers->add((string) $req->get('region'), (string) $req->get('endpoint'), (string) $req->get('pubkey', ''));

            return new JsonResponse(['ok' => true]);
        }

        return new JsonResponse(['peers' => $this->peers->list()]);
    }

    #[Route('/api/policy/federation/replicate', methods: ['POST'])]
    public function replicate(Request $req): JsonResponse
    {
        $name = (string) $req->get('name');
        $version = (int) $req->get('version', 1);
        $this->queue->markForReplication($name, $version);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/policy/federation/push', methods: ['POST'])]
    public function push(Request $req): JsonResponse
    {
        $payload = (string) ($req->get('payload') ?? '{}');
        $sig = (string) ($req->get('signature') ?? '');
        if (!$this->signer->verify($payload, $sig)) {
            return new JsonResponse(['ok' => false, 'error' => 'invalid_signature'], 401);
        }
        $data = json_decode($payload, true);
        $name = (string) ($data['name'] ?? 'tenant.policy');
        $content = (string) json_encode($data['content'] ?? ['policies' => []]);
        $this->db->insert('policy_registry', [
            'ts' => gmdate('c'), 'name' => $name, 'version' => ($data['version'] ?? 1), 'format' => 'json', 'content' => $content,
        ]);

        return new JsonResponse(['ok' => true]);
    }
}
