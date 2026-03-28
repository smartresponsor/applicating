<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CategoryCompletenessEvaluateCommand;
use App\Command\CategoryMediaReadinessEvaluateCommand;
use App\Command\CategoryMoveCommand;
use App\Command\CategoryPublicationQualityEvaluateCommand;
use App\Command\CategoryReviewAssignCommand;
use App\Command\CategoryReviewQueueListCommand;
use App\Command\CategorySyndicationCategoryGovernanceSummaryCommand;
use App\Command\CategorySyndicationDeliveryFailedListCommand;
use App\Command\CategorySyndicationDestinationGovernanceSummaryCommand;
use App\Command\CategorySyndicationDestinationHistoryCommand;
use App\Command\CategorySyndicationPackagePreviewCommand;
use App\Command\CategorySyndicationRetryScheduleCommand;
use App\Command\CategoryWorkflowTransitionCommand;
use App\RepositoryInterface\CategorySyndicationDeliveryRecordRepositoryInterface;
use App\ServiceInterface\CatalogCompletenessServiceInterface;
use App\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
use App\ServiceInterface\CatalogPublicationQualityServiceInterface;
use App\ServiceInterface\CatalogReviewAssignmentServiceInterface;
use App\ServiceInterface\CatalogReviewQueueServiceInterface;
use App\ServiceInterface\CatalogSyndicationCategoryGovernanceSummaryServiceInterface;
use App\ServiceInterface\CatalogSyndicationDestinationGovernanceSummaryServiceInterface;
use App\ServiceInterface\CatalogSyndicationHistoryServiceInterface;
use App\ServiceInterface\CatalogSyndicationPackageGateServiceInterface;
use App\ServiceInterface\CatalogSyndicationRetryServiceInterface;
use App\ServiceInterface\CatalogWorkflowTransitionServiceInterface;
use App\ServiceInterface\CategoryMoveInterface;
use PHPUnit\Framework\TestCase;

final class CategoryCliDiscoverabilityTest extends TestCase
{
    public function testK14CommandsExposeDescriptionAndHelp(): void
    {
        $commands = [
            new CategoryWorkflowTransitionCommand($this->createMock(CatalogWorkflowTransitionServiceInterface::class)),
            new CategoryMoveCommand($this->createMock(CategoryMoveInterface::class)),
            new CategoryReviewQueueListCommand($this->createMock(CatalogReviewQueueServiceInterface::class)),
            new CategoryReviewAssignCommand($this->createMock(CatalogReviewAssignmentServiceInterface::class)),
            new CategoryCompletenessEvaluateCommand($this->createMock(CatalogCompletenessServiceInterface::class)),
            new CategoryPublicationQualityEvaluateCommand($this->createMock(CatalogPublicationQualityServiceInterface::class)),
            new CategoryMediaReadinessEvaluateCommand($this->createMock(CatalogDestinationMediaReadinessServiceInterface::class)),
            new CategorySyndicationPackagePreviewCommand($this->createMock(CatalogSyndicationPackageGateServiceInterface::class)),
            new CategorySyndicationDeliveryFailedListCommand($this->createMock(CategorySyndicationDeliveryRecordRepositoryInterface::class)),
            new CategorySyndicationRetryScheduleCommand(
                $this->createMock(CategorySyndicationDeliveryRecordRepositoryInterface::class),
                $this->createMock(CatalogSyndicationRetryServiceInterface::class),
            ),
            new CategorySyndicationDestinationHistoryCommand($this->createMock(CatalogSyndicationHistoryServiceInterface::class)),
            new CategorySyndicationDestinationGovernanceSummaryCommand($this->createMock(CatalogSyndicationDestinationGovernanceSummaryServiceInterface::class)),
            new CategorySyndicationCategoryGovernanceSummaryCommand($this->createMock(CatalogSyndicationCategoryGovernanceSummaryServiceInterface::class)),
        ];

        foreach ($commands as $command) {
            self::assertNotSame('', trim((string) $command->getDescription()));
            self::assertStringContainsStringIgnoringCase('Use this command', (string) $command->getHelp());
        }
    }
}
