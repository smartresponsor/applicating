<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DataFixtures\ApplicationFixtures;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingDemoResetCommandTest extends KernelTestCase
{
    public function testCommandResetsDemoData(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationLifecycleServiceInterface $service */
        $service = $container->get(ApplicationLifecycleServiceInterface::class);
        $fixtures = new ApplicationFixtures($service);
        $fixtures->load($entityManager);

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = $container->get(TenantApplicationRepository::class);

        self::assertCount(6, $applicationRepository->findOrderedForAdmin());
        self::assertCount(6, $tenantApplicationRepository->findAll());

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:demo:reset');

        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Application demo data reset.', $tester->getDisplay());
        self::assertCount(0, $applicationRepository->findOrderedForAdmin());
        self::assertCount(0, $tenantApplicationRepository->findAll());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
