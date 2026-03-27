<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Component\Product\Service\Product\ProductRetryHandler;

final class ReliabilityLayerTest extends TestCase
{
    public function testBackoff()
    {
        $r = new ProductRetryHandler();
        $this->assertSame(100, $r->nextDelayMs(1));
        $this->assertSame(200, $r->nextDelayMs(2));
        $this->assertSame(400, $r->nextDelayMs(3));
        $this->assertSame(60000, $r->nextDelayMs(20)); // capped
    }
}
