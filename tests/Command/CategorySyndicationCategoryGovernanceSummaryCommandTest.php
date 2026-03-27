<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategorySyndicationCategoryGovernanceSummaryCommand;
use App\Event\CategorySyndicationCategoryGovernanceSummaryBuilt;
use App\ServiceInterface\CatalogSyndicationCategoryGovernanceSummaryServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategorySyndicationCategoryGovernanceSummaryCommandTest extends TestCase
{
    public function testExecutePrintsCategoryGovernanceSummary(): void
    {
        $service = $this->createMock(CatalogSyndicationCategoryGovernanceSummaryServiceInterface::class);
        $service->method('buildSummary')->willReturn(new CategorySyndicationCategoryGovernanceSummaryBuilt([
            'categoryId' => 'cat-1',
            'totalTrails' => 3,
            'destinationIds' => ['dest-1', 'dest-2'],
        ], new \DateTimeImmutable()));

        $tester = new CommandTester(new CategorySyndicationCategoryGovernanceSummaryCommand($service));
        $exitCode = $tester->execute([
            'categoryId' => 'cat-1',
            'actorId' => 'ops',
            'reason' => 'weekly-report',
            '--trails' => '[{"categoryId":"cat-1","destinationId":"dest-1"}]',
        ]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('"categoryId": "cat-1"', $tester->getDisplay());
    }
}
