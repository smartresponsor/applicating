<?php
declare(strict_types=1);

namespace App\Component\Product\Outbox\Product;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_outbox')]
class ProductOutbox
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'event_name', type: 'string', length: 128)]
    private string $eventName;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    #[ORM\Column(name: 'idempotency_key', type: 'string', length: 128, unique: true)]
    private string $idempotencyKey;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'processed_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    public function __construct(string $id, string $eventName, string $payload, string $idempotencyKey)
    {
        $this->id = $id;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->idempotencyKey = $idempotencyKey;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function eventName(): string { return $this->eventName; }
    public function payload(): string { return $this->payload; }
    public function idempotencyKey(): string { return $this->idempotencyKey; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function processedAt(): ?\DateTimeImmutable { return $this->processedAt; }
    public function markProcessed(): void { $this->processedAt = new \DateTimeImmutable(); }
}
