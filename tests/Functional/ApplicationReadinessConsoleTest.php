<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Command\ApplicationReadinessCommand;
use App\Applicating\DTO\ApplicationReadinessDTO;
use App\Applicating\DTO\ApplicationReadinessSignalsDTO;
use App\Applicating\ServiceInterface\ApplicationReadinessServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ApplicationReadinessConsoleTest extends TestCase
{
    public function testBlockedApplicationPrintsReasonsAndWarnings(): void
    {
        $service = $this->createMock(ApplicationReadinessServiceInterface::class);
        $service->expects(self::once())->method('buildReadiness')->with('blocked-app')->willReturn($this->readiness(false, ['No approved manifest'], ['Check governance']));
        $tester = new CommandTester(new ApplicationReadinessCommand($service));

        self::assertSame(Command::FAILURE, $tester->execute(['slug' => 'blocked-app']));
        self::assertStringContainsString('canPublish: false', $tester->getDisplay());
        self::assertStringContainsString(' - No approved manifest', $tester->getDisplay());
        self::assertStringContainsString(' - Check governance', $tester->getDisplay());
    }

    public function testEligibleApplicationReturnsSuccess(): void
    {
        $service = $this->createMock(ApplicationReadinessServiceInterface::class);
        $service->expects(self::once())->method('buildReadiness')->with('ready-app')->willReturn($this->readiness(true, [], []));
        $tester = new CommandTester(new ApplicationReadinessCommand($service));

        self::assertSame(Command::SUCCESS, $tester->execute(['slug' => 'ready-app']));
        self::assertStringContainsString('canPublish: true', $tester->getDisplay());
        self::assertStringNotContainsString('blockingReasons:', $tester->getDisplay());
    }

    /**
     * @param list<string> $blockingReasons
     * @param list<string> $warnings
     */
    private function readiness(bool $canPublish, array $blockingReasons, array $warnings): ApplicationReadinessDTO
    {
        return new ApplicationReadinessDTO(
            canPublish: $canPublish,
            blockingReasons: $blockingReasons,
            warnings: $warnings,
            signals: new ApplicationReadinessSignalsDTO(false, 'app', 0, 0, false, []),
        );
    }
}
