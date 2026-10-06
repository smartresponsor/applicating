<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\BuilderInterface\ApplicationAdminViewBuilderInterface;
use App\Applicating\Controller\ApplicationAdminController;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\DTO\ApplicationPublishEligibilityDTO;
use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Applicating\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ApplicationAdminControllerCoverageTest extends KernelTestCase
{
    public function testNeutralAdministrationPayloads(): void
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
        /** @var FormFactoryInterface $formFactory */
        $formFactory = $container->get('form.factory');
        /** @var RouterInterface $router */
        $router = $container->get('router');

        $sessionRequest = Request::create('/');
        $sessionRequest->setSession(new Session(new MockArraySessionStorage()));
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $requestStack->push($sessionRequest);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $controller = new ApplicationAdminController();
        $controller->setContainer(new ServiceLocator([
            'security.authorization_checker' => static fn (): AuthorizationCheckerInterface => $authorizationChecker,
            'form.factory' => static fn (): FormFactoryInterface => $formFactory,
            'router' => static fn (): RouterInterface => $router,
        ]));

        $application = new ApplicationEntity(
            'Admin Coverage Application',
            'admin-coverage-application',
            'applicating/admin-coverage-application',
            'Applicating Labs',
            'Admin coverage summary.',
        );
        $applicationRepository->save($application);

        $summary = new ApplicationSummaryDTO(1, 0, 0, 0, 0);
        $reportService = $this->createMock(ApplicationReportServiceInterface::class);
        $reportService->method('buildSummary')->willReturn($summary);

        $viewBuilder = $this->createMock(ApplicationAdminViewBuilderInterface::class);
        $viewBuilder->method('buildIndexRows')->willReturn([]);
        $viewBuilder->method('buildShowView')->willReturn(ApplicationAdminShowViewDTO::fromApplication($application, [], [], []));

        $lifecycleService = $this->createMock(ApplicationLifecycleServiceInterface::class);
        $eligibilityService = $this->createMock(ApplicationPublishEligibilityServiceInterface::class);
        $eligibilityService->method('buildEligibilityMap')->willReturn([
            new ApplicationPublishEligibilityDTO(42, true, null),
        ]);

        $index = $controller->index($applicationRepository, $reportService, $viewBuilder);
        self::assertSame('index', $this->operation($index));
        $indexData = $this->data($index);
        self::assertSame($summary, $indexData['summary']);
        self::assertSame([], $indexData['applicationRows']);

        $new = $controller->new(Request::create('/admin/applications/new', 'GET'), $lifecycleService);
        self::assertIsArray($new);
        self::assertSame('new', $this->operation($new));

        $report = $controller->report($reportService);
        self::assertSame('report', $this->operation($report));
        self::assertSame($summary, $this->data($report)['summary']);

        $show = $controller->show($application, $eligibilityService, $viewBuilder);
        self::assertSame('show', $this->operation($show));
        $showData = $this->data($show);
        self::assertSame([
            42 => ['eligible' => true, 'reason' => null],
        ], $showData['publishEligibility']);
        self::assertTrue($showData['canEdit']);
        self::assertTrue($showData['canPublish']);
        self::assertTrue($showData['canAssign']);

        $edit = $controller->edit(
            $application,
            Request::create('/admin/applications/edit/'.$application->getId(), 'GET'),
            $lifecycleService,
        );
        self::assertIsArray($edit);
        self::assertSame('edit', $this->operation($edit));
        self::assertSame($application, $this->data($edit)['application']);
    }

    /** @param array<string, mixed> $payload */
    private function operation(array $payload): string
    {
        $view = $payload['_view'] ?? null;
        self::assertIsArray($view);
        $operation = $view['operation'] ?? null;
        self::assertIsString($operation);

        return $operation;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<mixed>
     */
    private function data(array $payload): array
    {
        $data = $payload['data'] ?? null;
        self::assertIsArray($data);

        return $data;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
