<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ImportMapAuditCommandTest extends KernelTestCase
{
    public function testCommandReportsNoImportmapAssetsInActiveRuntime(): void
    {
        self::bootKernel();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('importmap:audit');

        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString(
            'No importmap assets are defined in the active Applicating runtime.',
            $tester->getDisplay()
        );
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
