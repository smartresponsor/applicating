<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingTenantAssignmentCheckCommandTest extends KernelTestCase
{
    public function testCommandPrintsTenantAssignmentConsistency(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Tenant Check Application',
            'tenant-check-application',
            'applicating/tenant-check-application',
            'Applicating Labs',
            'Tenant check summary'
        );
        $tenantApplication = new TenantApplication(
            $application,
            'tenant-check',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $entityManager->persist($application);
        $entityManager->persist($tenantApplication);
        $entityManager->flush();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:tenant:assignment:check');

        $tester = new CommandTester($command);
        $tester->execute([
            'tenantKey' => 'tenant-check',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString(
            'tenant-check:tenant-check-application:1.0.0:installed',
            $tester->getDisplay()
        );
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
