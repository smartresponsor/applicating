<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Command\ApplicationEvaluateReadinessCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ApplicationEvaluateReadinessConsoleTest extends TestCase
{
    public function testSoftEvaluationReportsProfileAndSuccessfulVerdict(): void
    {
        $tester = new CommandTester(new ApplicationEvaluateReadinessCommand());

        $exitCode = $tester->execute([
            '--eval-profile' => 'soft',
            '--min-score' => '-5',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Profile: soft', $tester->getDisplay());
        self::assertStringContainsString('Should fail: false', $tester->getDisplay());
        self::assertSame('0', $_ENV['APP_READINESS_EVAL_MIN_SCORE']);
        self::assertSame('soft', $_ENV['APP_READINESS_EVAL_PROFILE']);
    }

    public function testStrictEvaluationClampsOversizedThreshold(): void
    {
        $tester = new CommandTester(new ApplicationEvaluateReadinessCommand());

        $exitCode = $tester->execute([
            '--eval-profile' => 'strict',
            '--min-score' => '8',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Profile: strict', $tester->getDisplay());
        self::assertSame('1', $_ENV['APP_READINESS_EVAL_MIN_SCORE']);
        self::assertSame('strict', $_ENV['APP_READINESS_EVAL_PROFILE']);
    }
}
