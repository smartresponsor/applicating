<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace\Controller;

use App\Component\Product\Marketplace\BillingBridge;
use App\Component\Product\Marketplace\Installer;
use App\Component\Product\Marketplace\ManifestParser;
use App\Component\Product\Marketplace\Registry;
use App\Component\Product\Marketplace\Sandbox\SandboxRunner;
use App\Component\Product\Marketplace\SignatureVerifier;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class MarketplaceAPI
{
    public function __construct(
        private readonly Connection $db,
        private readonly ManifestParser $parser,
        private readonly Registry $registry,
        private readonly Installer $installer,
        private readonly SignatureVerifier $sig,
        private readonly BillingBridge $billing,
        private readonly SandboxRunner $sandbox,
    ) {
    }

    #[Route('/api/marketplace/publish', methods: ['POST'])]
    public function publish(Request $req): JsonResponse
    {
        $manifest = (string) ($req->get('manifest') ?? '{}');
        $signature = (string) ($req->get('signature') ?? '');
        if (!$this->sig->verify($manifest, $signature, 'demo')) {
            return new JsonResponse(['ok' => false, 'error' => 'invalid signature'], 400);
        }
        $m = $this->parser->parse($manifest);
        $id = $this->registry->publish($m, $signature);

        return new JsonResponse(['ok' => true, 'plugin_id' => $id]);
    }

    #[Route('/api/marketplace/list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(['plugins' => $this->registry->list()]);
    }

    #[Route('/api/marketplace/install', methods: ['POST'])]
    public function install(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $pluginId = (int) ($req->get('plugin_id') ?? 1);
        $version = (string) ($req->get('version') ?? '1.0.0');
        // списать подписку (демо: берём price из каталога)
        $price = (float) ($this->db->fetchOne('SELECT price_usd FROM marketplace_plugins WHERE id=?', [$pluginId]) ?? 0.0);
        if ($price > 0) {
            $this->billing->subscribe($tenant, $pluginId, $price);
        }
        $ok = $this->installer->install($tenant, $pluginId, $version);

        return new JsonResponse(['ok' => $ok]);
    }

    #[Route('/api/marketplace/run', methods: ['POST'])]
    public function run(Request $req): JsonResponse
    {
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $pluginId = (int) ($req->get('plugin_id') ?? 1);
        $entry = (string) ($req->get('entry') ?? 'run');
        $args = (array) ($req->get('args') ?? []);
        $res = $this->sandbox->run($tenant, $pluginId, $entry, $args);

        return new JsonResponse($res);
    }
}
