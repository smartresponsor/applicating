<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingDiagnosticsRunCommandTest extends KernelTestCase
{
    public function testCommandPrintsTenantDiagnostics(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Diagnostics Command Application',
            'diagnostics-command-application',
            'applicating/diagnostics-command-application',
            'Applicating Labs',
            'Diagnostics command summary'
        );

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.diagnostics.command',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.diagnostics.command']
        );
        $application->addManifest($manifest);

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'diagnostics-command'),
            'https://downloads.example.test/diagnostics-command/1.0.0.zip',
            'Diagnostics command release notes'
        );
        $application->addRelease($release);

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-diagnostics-command',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $entityManager->persist($application);
        $entityManager->persist($manifest);
        $entityManager->persist($release);
        $entityManager->persist($tenantApplication);
        $entityManager->flush();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:diagnostics:run');

        $tester = new CommandTester($command);
        $tester->execute([
            'tenantKey' => 'tenant-diagnostics-command',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        self::assertStringContainsString('tenant-diagnostics-command', $display);
        self::assertStringContainsString('diagnostics-command-application', $display);
        self::assertStringContainsString('"enabled":true', str_replace(' ', '', $display));
        self::assertStringContainsString('"billingActive":true', str_replace(' ', '', $display));
        self::assertStringContainsString('"manifest_present":true', str_replace(' ', '', $display));
        self::assertStringContainsString('"release_present":true', str_replace(' ', '', $display));
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
