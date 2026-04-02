<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ApplicatingApplicationEvaluateReadinessCommandTest extends KernelTestCase
{
    public function testCommandRuns(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);
        $command = $application->find('applicating:application:evaluate-readiness');

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        self::assertTrue(in_array($exitCode, [0, 1], true));
        self::assertStringContainsString('missing_application', $tester->getDisplay());
    }
}
