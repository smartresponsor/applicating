<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingFixturesLoadDemoCommandTest extends KernelTestCase
{
    public function testCommandLoadsDemoFixtures(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:fixtures:load-demo');

        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Demo fixtures loaded.', $tester->getDisplay());

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = $container->get(TenantApplicationRepository::class);

        self::assertCount(6, $applicationRepository->findOrderedForAdmin());
        self::assertSame(4, $applicationRepository->countPublished());
        self::assertCount(6, $tenantApplicationRepository->findAll());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
