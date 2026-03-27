<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Component\Product\ValueObject\Sku;
use App\Component\Product\ValueObject\Money;
use App\Component\Product\Entity\Product;

final class BasicSmokeTest extends TestCase
{
    public function testSkuUppercase(): void
    {
        $sku = new Sku('abc-123');
        $this->assertSame('ABC-123', (string)$sku);
    }

    public function testProductPriceAndStock(): void
    {
        $p = new Product(new Sku('SKU1'), new Money(1000, 'USD'), 5);
        $this->assertSame(5, $p->stock());
        $p->adjustStock(3);
        $this->assertSame(8, $p->stock());
        $p->changePrice(new Money(1500, 'USD'));
        $this->assertSame(1500, $p->price()->amount());
    }
}
