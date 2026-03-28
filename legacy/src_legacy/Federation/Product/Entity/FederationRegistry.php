<?php

declare(strict_types=1);

namespace App\Federation\Product\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name: 'federation_registry')] class FederationRegistry
{
    #[ORM\Id,ORM\GeneratedValue,ORM\Column(type: 'bigint')] private ?string $id = null;
    #[ORM\Column(type: 'text', unique: true)] private string $nodeId;
    #[ORM\Column(type: 'text')] private string $baseUrl;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $publicKey;
    #[ORM\Column(type: 'string', length: 16, nullable: true)] private ?string $region;
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable')] private \DateTimeImmutable $lastSeen;
    public function __construct(string $nid, string $url, ?string $pk, ?string $r, array $m = [])
    {
        $this->nodeId = $nid;
        $this->baseUrl = rtrim($url, '/');
        $this->publicKey = $pk;
        $this->region = $r;
        $this->meta = $m;
        $this->lastSeen = new \DateTimeImmutable('now');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getNodeId(): string
    {
        return $this->nodeId;
    }
}
