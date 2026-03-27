<?php

declare(strict_types=1);

namespace App\Component\Product\DeveloperHub;

use App\Component\Product\Marketplace\ManifestParser;
use App\Component\Product\Marketplace\Registry;
use App\Component\Product\Marketplace\SignatureVerifier;
use Doctrine\DBAL\Connection;

final class Publisher
{
    public function __construct(
        private readonly Connection $db,
        private readonly ManifestParser $parser,
        private readonly SignatureVerifier $sig,
        private readonly Registry $registry,
    ) {
    }

    public function publish(string $apiKey, string $manifestJson, string $pubKey = 'dev'): int
    {
        // audit
        $this->db->insert('devhub_audit', ['ts' => gmdate('c'), 'event' => 'publish', 'actor' => $apiKey, 'meta' => $manifestJson]);
        $signature = hash('sha256', $manifestJson.'#'.$pubKey);
        if (!$this->sig->verify($manifestJson, $signature, $pubKey)) {
            throw new \RuntimeException('signature failed');
        }
        $m = $this->parser->parse($manifestJson);

        return $this->registry->publish($m, $signature);
    }
}
