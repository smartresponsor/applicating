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

final class ApplicatingReportSummaryCommandTest extends KernelTestCase
{
    public function testCommandPrintsApplicationLifecycleSummary(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Report Command Application',
            'report-command-application',
            'applicating/report-command-application',
            'Applicating Labs',
            'Report command summary'
        );
        $application->publish();

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-report-command',
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
        $command = $console->find('applicating:report:summary');

        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        self::assertStringContainsString('applicationsTotal=1', $display);
        self::assertStringContainsString('applicationsPublished=1', $display);
        self::assertStringContainsString('tenantAssignmentsTotal=1', $display);
        self::assertStringContainsString('tenantAssignmentsEnabled=1', $display);
        self::assertStringContainsString('billingActiveTotal=1', $display);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
