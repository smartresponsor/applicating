<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationStateTransitionFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ApplicationRepository $applicationRepository;
    private TenantApplicationRepository $tenantApplicationRepository;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
        $this->entityManager = $entityManager;

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = static::getContainer()->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;

        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = static::getContainer()->get(TenantApplicationRepository::class);
        $this->tenantApplicationRepository = $tenantApplicationRepository;
    }

    public function testManagerCanPublishApplicationRelease(): void
    {
        [$application, $release] = $this->createDraftApplicationAggregate();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/publish/' . $release->getId());

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertSame('published', $reloaded->getPublicationState()->value);
        self::assertSame('published', $reloaded->getReleases()->first()->getPublicationState()->value);
    }

    public function testManagerCanSuspendPublishedApplication(): void
    {
        [$application] = $this->createPublishedApplicationAggregate();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/suspend');

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertSame('suspended', $reloaded->getPublicationState()->value);
    }

    public function testManagerCanDisableTenantApplication(): void
    {
        [, , $tenantApplication] = $this->createPublishedApplicationAggregate();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/tenant-assignment/' . $tenantApplication->getId() . '/toggle', [
            'enabled' => '0',
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $reloaded = $this->tenantApplicationRepository->find($tenantApplication->getId());
        self::assertInstanceOf(TenantApplication::class, $reloaded);
        self::assertFalse($reloaded->isEnabled());
        self::assertSame('disabled', $reloaded->getInstallationState()->value);
    }

    public function testManagerCanEnableDisabledTenantApplication(): void
    {
        [, , $tenantApplication] = $this->createPublishedApplicationAggregate(false);

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/tenant-assignment/' . $tenantApplication->getId() . '/toggle', [
            'enabled' => '1',
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $reloaded = $this->tenantApplicationRepository->find($tenantApplication->getId());
        self::assertInstanceOf(TenantApplication::class, $reloaded);
        self::assertTrue($reloaded->isEnabled());
        self::assertSame('installed', $reloaded->getInstallationState()->value);
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createDraftApplicationAggregate(): array
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Transition Demo ' . $suffix,
            'transition-demo-' . $suffix,
            'applicating/transition-demo-' . $suffix,
            'Applicating Labs',
            'Transition listing summary'
        );

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'transition-release-' . $suffix),
            'https://downloads.example.test/transition-demo/' . $suffix . '/1.0.0.zip',
            'Transition release notes'
        );
        $application->addRelease($release);

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.transition.' . $suffix,
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.transition.' . $suffix]
        );
        $application->addManifest($manifest);

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);
        $this->entityManager->persist($manifest);
        $this->entityManager->flush();

        return [$application, $release];
    }

    /** @return array{0: Application, 1: ApplicationRelease, 2: TenantApplication} */
    private function createPublishedApplicationAggregate(bool $tenantEnabled = true): array
    {
        [$application, $release] = $this->createDraftApplicationAggregate();
        $application->publish();
        $release->publish();

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-' . bin2hex(random_bytes(4)),
            '1.0.0',
            $tenantEnabled,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return [$application, $release, $tenantApplication];
    }
}
