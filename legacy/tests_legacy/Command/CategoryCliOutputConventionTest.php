<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Command;

use App\Command\CategoryReviewQueueListCommand;
use App\ServiceInterface\CatalogReviewQueueServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryCliOutputConventionTest extends TestCase
{
    public function testRejectsUnsupportedFormatWithMachineReadablePayload(): void
    {
        /** @var CatalogReviewQueueServiceInterface&MockObject $service */
        $service = $this->createMock(CatalogReviewQueueServiceInterface::class);
        $service->method('queueForReviewer')->willReturn([]);

        $command = new CategoryReviewQueueListCommand($service);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'reviewer' => 'reviewer-1',
            '--format' => 'xml',
        ]);

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString('"error":"invalid_format"', $tester->getDisplay());
    }
}
