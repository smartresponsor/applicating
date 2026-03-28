<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\ValueObject\ApplicationSlug;
use PHPUnit\Framework\TestCase;

final class ApplicationSlugTest extends TestCase
{
    public function testAcceptsCanonicalSlug(): void
    {
        self::assertSame('demo-application', (string) new ApplicationSlug('demo-application'));
    }

    public function testRejectsInvalidSlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ApplicationSlug('Demo Application');
    }
}
