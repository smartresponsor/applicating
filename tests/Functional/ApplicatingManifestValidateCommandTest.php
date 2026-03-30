<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingManifestValidateCommandTest extends KernelTestCase
{
    public function testCommandPrintsNormalizedManifestPayload(): void
    {
        self::bootKernel();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:manifest:validate');

        $tester = new CommandTester($command);
        $tester->execute([
            'identifier' => 'io.applicating.demo.application',
            'capabilities' => 'catalog,reporting,analytics',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        self::assertStringContainsString('Manifest payload normalized.', $display);
        self::assertStringContainsString('"identifier": "io.applicating.demo.application"', $display);
        self::assertStringContainsString('"capabilities": [', $display);
        self::assertStringContainsString('"catalog"', $display);
        self::assertStringContainsString('"reporting"', $display);
        self::assertStringContainsString('"analytics"', $display);
        self::assertStringContainsString('"sandboxProfile": "default"', $display);
        self::assertStringContainsString('"governanceState": "approved"', $display);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
