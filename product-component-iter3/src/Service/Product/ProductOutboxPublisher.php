<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use App\Component\Product\Interface\Product\ProductEventInterface;
use App\Component\Product\Outbox\Product\ProductOutbox;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class ProductOutboxPublisher
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function put(ProductEventInterface $event, ?string $forcedKey = null): void
    {
        $payload = json_encode($event->payload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $key = $forcedKey ?? $this->makeKey($event->eventName(), $event->payload());
        $record = new ProductOutbox(Uuid::v4()->toRfc4122(), $event->eventName(), (string)$payload, $key);
        $this->em->persist($record);
        // flush делается на уровне транзакции вызывающего сервиса
    }

    /** @param array<string,mixed> $payload */
    private function makeKey(string $eventName, array $payload): string
    {
        $base = $eventName . '|' . ($payload['product_id'] ?? '') . '|' . ($payload['product_sku'] ?? '');
        return hash('sha256', $base);
    }
}
