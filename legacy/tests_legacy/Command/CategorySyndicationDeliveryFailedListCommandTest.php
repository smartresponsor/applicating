<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategorySyndicationDeliveryFailedListCommand;
use App\Entity\CategorySyndicationDeliveryRecord;
use App\RepositoryInterface\CategorySyndicationDeliveryRecordRepositoryInterface;
use App\ValueObject\CategorySyndicationDeliveryStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategorySyndicationDeliveryFailedListCommandTest extends TestCase
{
    public function testExecutePrintsNdjsonForFailedRecords(): void
    {
        $record = new CategorySyndicationDeliveryRecord('d-1', 'p-1', 'dest-1', 'cat-1', new CategorySyndicationDeliveryStatus('failed'), 2, 503, 'boom', null);
        $repo = $this->createMock(CategorySyndicationDeliveryRecordRepositoryInterface::class);
        $repo->method('failedRecords')->willReturn([$record]);

        $tester = new CommandTester(new CategorySyndicationDeliveryFailedListCommand($repo));
        $tester->execute([]);

        self::assertStringContainsString('"deliveryId":"d-1"', str_replace(' ', '', $tester->getDisplay()));
    }
}
