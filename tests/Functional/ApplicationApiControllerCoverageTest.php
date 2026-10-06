<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Controller\ApplicationApiController;
use App\Applicating\Controller\ApplicationReadinessApiController;
use App\Applicating\DTO\ApplicationReadinessDTO;
use App\Applicating\DTO\ApplicationReadinessSignalsDTO;
use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationReadinessServiceInterface;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ApplicationApiControllerCoverageTest extends KernelTestCase
{
    public function testApplicationAndReadinessJsonResponses(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        $applicationRepository->save(new ApplicationEntity(
            'API Coverage Application',
            'api-coverage-application',
            'applicating/api-coverage-application',
            'Applicating Labs',
            'API coverage summary.',
        ));

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);
        $controller = new ApplicationApiController();
        $controller->setContainer(new ServiceLocator([
            'security.authorization_checker' => static fn (): AuthorizationCheckerInterface => $authorizationChecker,
        ]));

        $indexContent = $controller->index($applicationRepository)->getContent();
        self::assertIsString($indexContent);
        self::assertStringContainsString('"slug":"api-coverage-application"', $indexContent);

        $reportService = $this->createMock(ApplicationReportServiceInterface::class);
        $reportService->method('buildSummary')->willReturn(new ApplicationSummaryDTO(1, 0, 0, 0, 0));
        $reportContent = $controller->report($reportService)->getContent();
        self::assertIsString($reportContent);
        self::assertStringContainsString('"applicationsTotal":1', $reportContent);

        $readinessService = $this->createMock(ApplicationReadinessServiceInterface::class);
        $readinessService->expects(self::once())->method('buildReadiness')->with('api-coverage-application')->willReturn(
            new ApplicationReadinessDTO(
                true,
                [],
                [],
                new ApplicationReadinessSignalsDTO(true, 'api-coverage-application', 1, 1, true, []),
            ),
        );
        $readinessController = new ApplicationReadinessApiController($readinessService);
        $readinessController->setContainer(new ServiceLocator([
            'security.authorization_checker' => static fn (): AuthorizationCheckerInterface => $authorizationChecker,
        ]));
        $readinessContent = $readinessController('api-coverage-application')->getContent();
        self::assertIsString($readinessContent);
        self::assertStringContainsString('"canPublish":true', $readinessContent);
        self::assertStringContainsString('"applicationSlug":"api-coverage-application"', $readinessContent);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
