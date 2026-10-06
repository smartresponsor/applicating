<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Command;

use App\Applicating\Command\ApplicationReadinessCommand;
use App\Applicating\Command\ApplicationReportSummaryCommand;
use App\Applicating\Command\ApplicationRuntimeSetCommand;
use App\Applicating\DTO\ApplicationReadinessDTO;
use App\Applicating\DTO\ApplicationReadinessSignalsDTO;
use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\ServiceInterface\ApplicationReadinessServiceInterface;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use App\Applicating\ServiceInterface\ApplicationRuntimeAssignmentServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ApplicationOperationalCommandTest extends TestCase
{
    public function testReadinessCommandReportsSuccessfulPublicationState(): void
    {
        $service = $this->createMock(ApplicationReadinessServiceInterface::class);
        $service->expects(self::once())
            ->method('buildReadiness')
            ->with('demo')
            ->willReturn(new ApplicationReadinessDTO(
                true,
                [],
                ['runtime assignment uses host shared mode'],
                new ApplicationReadinessSignalsDTO(true, 'demo', 1, 1, true, []),
            ));

        $tester = new CommandTester(new ApplicationReadinessCommand($service));
        $status = $tester->execute(['slug' => 'demo']);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('Application readiness for demo', $tester->getDisplay());
        self::assertStringContainsString('canPublish: true', $tester->getDisplay());
        self::assertStringContainsString('warnings:', $tester->getDisplay());
        self::assertStringContainsString('runtime assignment uses host shared mode', $tester->getDisplay());
    }

    public function testReadinessCommandReportsBlockingFailure(): void
    {
        $service = $this->createMock(ApplicationReadinessServiceInterface::class);
        $service->method('buildReadiness')->willReturn(new ApplicationReadinessDTO(
            false,
            ['No approved manifest is available.'],
            [],
            new ApplicationReadinessSignalsDTO(true, 'blocked', 1, 0, false, []),
        ));

        $tester = new CommandTester(new ApplicationReadinessCommand($service));
        $status = $tester->execute(['slug' => 'blocked']);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('canPublish: false', $tester->getDisplay());
        self::assertStringContainsString('blockingReasons:', $tester->getDisplay());
        self::assertStringContainsString('No approved manifest is available.', $tester->getDisplay());
    }

    public function testRuntimeSetCommandAppliesCanonicalRuntimeMode(): void
    {
        $application = $this->application('demo');
        $assignment = new ApplicationRuntimeAssignmentEntity($application, 'production', ApplicationRuntimeMode::CustomDomain);

        $service = $this->createMock(ApplicationRuntimeAssignmentServiceInterface::class);
        $service->expects(self::once())
            ->method('setMode')
            ->with('demo', 'production', ApplicationRuntimeMode::CustomDomain)
            ->willReturn($assignment);

        $tester = new CommandTester(new ApplicationRuntimeSetCommand($service));
        $status = $tester->execute([
            'applicationSlug' => 'demo',
            'environment' => 'production',
            'mode' => 'custom_domain',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('demo:production => custom_domain', $tester->getDisplay());
    }

    public function testRuntimeSetCommandRejectsUnknownRuntimeMode(): void
    {
        $service = $this->createMock(ApplicationRuntimeAssignmentServiceInterface::class);
        $service->expects(self::never())->method('setMode');

        $tester = new CommandTester(new ApplicationRuntimeSetCommand($service));
        $status = $tester->execute([
            'applicationSlug' => 'demo',
            'environment' => 'production',
            'mode' => 'unknown',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertNotSame('', trim($tester->getDisplay()));
    }

    public function testRuntimeSetCommandReportsServiceDomainFailure(): void
    {
        $service = $this->createMock(ApplicationRuntimeAssignmentServiceInterface::class);
        $service->method('setMode')->willThrowException(new \DomainException('Runtime transition rejected.'));

        $tester = new CommandTester(new ApplicationRuntimeSetCommand($service));
        $status = $tester->execute([
            'applicationSlug' => 'demo',
            'environment' => 'production',
            'mode' => 'host_shared',
        ]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Runtime transition rejected.', $tester->getDisplay());
    }

    public function testReportSummaryCommandEmitsStableCounters(): void
    {
        $service = $this->createMock(ApplicationReportServiceInterface::class);
        $service->expects(self::once())
            ->method('buildSummary')
            ->willReturn(new ApplicationSummaryDTO(5, 3, 8, 6, 4));

        $tester = new CommandTester(new ApplicationReportSummaryCommand($service));
        $status = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('applicationsTotal=5', $tester->getDisplay());
        self::assertStringContainsString('applicationsPublished=3', $tester->getDisplay());
        self::assertStringContainsString('tenantAssignmentsTotal=8', $tester->getDisplay());
        self::assertStringContainsString('tenantAssignmentsEnabled=6', $tester->getDisplay());
        self::assertStringContainsString('billingActiveTotal=4', $tester->getDisplay());
    }

    private function application(string $slug): ApplicationEntity
    {
        return new ApplicationEntity(
            'Demo Application',
            $slug,
            'vendor/'.$slug,
            'Developer',
            'Application summary.',
        );
    }
}
