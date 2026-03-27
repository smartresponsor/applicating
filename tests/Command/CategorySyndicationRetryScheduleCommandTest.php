<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategorySyndicationRetryScheduleCommand;
use App\Entity\CategorySyndicationDeliveryRecord;
use App\Event\CategorySyndicationRetryScheduled;
use App\RepositoryInterface\CategorySyndicationDeliveryRecordRepositoryInterface;
use App\ServiceInterface\CatalogSyndicationRetryServiceInterface;
use App\ValueObject\CategorySyndicationDeliveryStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class CategorySyndicationRetryScheduleCommandTest extends TestCase
{
    public function testExecuteSchedulesRetryAndPrintsJson(): void
    {
        $record = new CategorySyndicationDeliveryRecord('d-1', 'p-1', 'dest-1', 'cat-1', new CategorySyndicationDeliveryStatus('failed'), 1, 503, 'boom', null);
        $repo = $this->createMock(CategorySyndicationDeliveryRecordRepositoryInterface::class);
        $repo->method('find')->with('d-1')->willReturn($record);

        $service = $this->createMock(CatalogSyndicationRetryServiceInterface::class);
        $service->method('scheduleRetry')->willReturn(new CategorySyndicationRetryScheduled([
            'deliveryId' => 'd-1', 'nextAttempt' => 2, 'delaySeconds' => 300,
        ], new \DateTimeImmutable()));

        $tester = new CommandTester(new CategorySyndicationRetryScheduleCommand($repo, $service));
        $status = $tester->execute(['deliveryId' => 'd-1', 'actorId' => 'ops', 'reason' => 'retry']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('"nextAttempt": 2', $tester->getDisplay());
    }
}
