<?php
declare(strict_types=1);

namespace App\Component\Product\Idempotency\Product;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_idempotency')]
class ProductIdempotency
{
    #[ORM\Id]
    #[ORM\Column(name: 'key_hash', type: 'string', length: 64)]
    private string $keyHash;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'processed', type: 'boolean')]
    private bool $processed = false;

    public function __construct(string $keyHash)
    {
        $this->keyHash = $keyHash;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function keyHash(): string { return $this->keyHash; }
    public function processed(): bool { return $this->processed; }
    public function markProcessed(): void { $this->processed = true; }
}
