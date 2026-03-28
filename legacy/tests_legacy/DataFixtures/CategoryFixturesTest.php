<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

final class CategoryFixturesTest extends TestCase
{
    private function skipWhenUidComponentMissing(): void
    {
        if (!class_exists(\Symfony\Component\Uid\Ulid::class)) {
            self::markTestSkipped('symfony/uid is not installed in this environment.');
        }
    }

    public function testLoadPersistsDatasetAndFlushes(): void
    {
        $this->skipWhenUidComponentMissing();

        $persisted = 0;
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::exactly(42))
            ->method('persist')
            ->willReturnCallback(static function () use (&$persisted): void {
                ++$persisted;
            });
        $manager->expects(self::once())->method('flush');

        (new CategoryFixtures())->load($manager);

        self::assertSame(42, $persisted);
    }
}
