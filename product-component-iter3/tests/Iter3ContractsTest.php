<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Component\Product\ValueObject\Product\Sku;
use App\Component\Product\ValueObject\Product\Money;
use App\Component\Product\Event\Product\ProductCreatedEvent;
use App\Component\Product\Entity\Product\Product;

final class Iter3ContractsTest extends TestCase
{
    public function testSkuUppercase(): void
    {
        $sku = new Sku('abc-123');
        $this->assertSame('ABC-123', (string)$sku);
    }

    public function testMoneyEquals(): void
    {
        $a = new Money(100, 'USD');
        $b = new Money(100, 'USD');
        $this->assertTrue($a->equals($b));
    }

    public function testEventPayloadShape(): void
    {
        $p = new Product(new Sku('SKU1'), new Money(1000, 'USD'), 0);
        $ev = new ProductCreatedEvent($p);
        $payload = $ev->payload();
        $this->assertArrayHasKey('product_id', $payload);
        $this->assertArrayHasKey('product_sku', $payload);
        $this->assertArrayHasKey('product_status', $payload);
    }
}
