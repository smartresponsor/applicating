<?php
declare(strict_types=1);

namespace App\Component\Product\DeadLetter\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_dead_letter')]
class ProductDeadLetter
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'event_name', type: 'string', length: 128)]
    private string $eventName;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    #[ORM\Column(name: 'error', type: 'text')]
    private string $error;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'retries', type: 'integer')]
    private int $retries = 0;

    #[ORM\Column(name: 'last_retry_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastRetryAt = null;

    public function __construct(string $eventName, string $payload, string $error)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->error = $error;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function eventName(): string { return $this->eventName; }
    public function payload(): string { return $this->payload; }
    public function error(): string { return $this->error; }
    public function retries(): int { return $this->retries; }
    public function incrementRetries(): void { $this->retries++; $this->lastRetryAt = new \DateTimeImmutable(); }
}
