<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Controller\ApplicationAdminLifecycleController;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
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

final class ApplicationAdminLifecycleControllerCoverageTest extends KernelTestCase
{
    public function testLifecycleMutationAndInvalidFormPaths(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new ApplicationEntity(
            'Lifecycle Controller Application',
            'lifecycle-controller-application',
            'applicating/lifecycle-controller-application',
            'Applicating Labs',
            'Lifecycle controller coverage.',
        );
        $release = new ApplicationReleaseEntity(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'lifecycle-controller'),
            'https://downloads.example.test/lifecycle-controller/1.0.0.zip',
            'Lifecycle controller release notes.',
        );
        $application->addRelease($release);
        $assignment = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-controller',
            '1.0.0',
            false,
            true,
            [],
        );

        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($assignment);
        $entityManager->flush();

        self::assertNotNull($application->getId());
        self::assertNotNull($release->getId());

        /** @var FormFactoryInterface $formFactory */
        $formFactory = $container->get('form.factory');
        /** @var RouterInterface $router */
        $router = $container->get('router');
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        $sessionRequest = Request::create('/');
        $sessionRequest->setSession(new Session(new MockArraySessionStorage()));
        $requestStack->push($sessionRequest);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')->willReturn(true);

        $controller = new ApplicationAdminLifecycleController();
        $controller->setContainer(new ServiceLocator([
            'security.authorization_checker' => static fn (): AuthorizationCheckerInterface => $authorizationChecker,
            'form.factory' => static fn (): FormFactoryInterface => $formFactory,
            'router' => static fn (): RouterInterface => $router,
            'request_stack' => static fn (): RequestStack => $requestStack,
        ]));

        $lifecycleService = $this->createMock(ApplicationLifecycleServiceInterface::class);
        $lifecycleService->expects(self::once())
            ->method('publishApplication')
            ->with($application, $release);
        $lifecycleService->expects(self::once())
            ->method('suspendApplication')
            ->with($application);
        $lifecycleService->expects(self::once())
            ->method('toggleTenantApplication')
            ->with($assignment, true);

        $publish = $controller->publish($application, $release->getId(), $lifecycleService);
        self::assertSame(302, $publish->getStatusCode());

        $suspend = $controller->suspend($application, $lifecycleService);
        self::assertSame(302, $suspend->getStatusCode());

        $toggle = $controller->toggle(
            $assignment,
            Request::create('/admin/applications/tenant/assignment/toggle', 'POST', ['enabled' => '1']),
            $lifecycleService,
        );
        self::assertSame(302, $toggle->getStatusCode());

        $releaseForm = $controller->createRelease(
            $application,
            Request::create('/admin/applications/release', 'POST', ['application_release' => [
                'version' => 'bad',
                'channel' => 'stable',
                'checksum' => 'checksum',
                'downloadUrl' => 'not-a-url',
                'releaseNotes' => 'notes',
            ]]),
            $lifecycleService,
        );
        self::assertSame(302, $releaseForm->getStatusCode());

        $manifestForm = $controller->createManifest(
            $application,
            Request::create('/admin/applications/manifest', 'POST', ['application_manifest' => [
                'manifestVersion' => '1.0.0',
                'identifier' => 'invalid',
                'capabilities' => 'listing',
                'permissions' => 'tenant:read',
                'runtimeHooks' => 'bootstrap',
                'sandboxProfile' => 'default',
                'governanceState' => 'approved',
            ]]),
            $lifecycleService,
        );
        self::assertSame(302, $manifestForm->getStatusCode());

        $assignmentForm = $controller->assign(
            $application,
            Request::create('/admin/applications/assign', 'POST', ['application_tenant_assignment' => [
                'tenantKey' => 'tenant-controller',
                'installedVersion' => 'bad',
                'enabled' => '1',
                'billingActive' => '1',
                'accessPolicy' => '{}',
            ]]),
            $lifecycleService,
        );
        self::assertSame(302, $assignmentForm->getStatusCode());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
