<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Component\Product\ValueObject\Product\Sku;
use App\Component\Product\ValueObject\Product\Money;

final class ContractsSmokeTest extends TestCase
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
}
