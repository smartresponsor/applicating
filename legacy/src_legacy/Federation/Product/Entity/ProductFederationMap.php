<?php

declare(strict_types=1);

namespace App\Federation\Product\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] #[ORM\Table(name: 'product_federation_map')] class ProductFederationMap
{
    #[ORM\Id,ORM\GeneratedValue,ORM\Column(type: 'bigint')] private ?string $id = null;
    #[ORM\Column(type: 'bigint')] private string $productId;
    #[ORM\Column(type: 'text', unique: true)] private string $federationId;
    #[ORM\Column(type: 'text')] private string $tenantId;
    #[ORM\Column(type: 'string', length: 16, nullable: true)] private ?string $region = null;
    #[ORM\Column(type: 'json')] private array $scope = [];
    #[ORM\Column(type: 'datetime_immutable')] private \DateTimeImmutable $lastSynced;
    public function __construct(int $pid, string $fid, string $tid, ?string $r, array $s)
    {
        $this->productId = (string) $pid;
        $this->federationId = $fid;
        $this->tenantId = $tid;
        $this->region = $r;
        $this->scope = $s;
        $this->lastSynced = new \DateTimeImmutable('now');
    }

    public function getProductId(): int
    {
        return (int) $this->productId;
    }

    public function getFederationId(): string
    {
        return $this->federationId;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function touchSynced(): void
    {
        $this->lastSynced = new \DateTimeImmutable('now');
    }

    public function getLastSynced(): \DateTimeImmutable
    {
        return $this->lastSynced;
    }
}
