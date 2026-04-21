<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingApplicationEvaluateReadinessCommandTest extends KernelTestCase
{
    public function testCommandOutputsSummary(): void
    {
        self::bootKernel();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $application = new Application($kernel);
        $command = $application->find('applicating:application:evaluate-readiness');

        $tester = new CommandTester($command);
        $tester->execute(['--min-score' => '0.0']);

        $display = $tester->getDisplay();

        self::assertStringContainsString('Evaluation total', $display);
        self::assertStringContainsString('Score', $display);
    }
}
