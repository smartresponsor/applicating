<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Command;

use App\Applicating\Command\ApplicationImportMapAuditCommand;
use App\Applicating\Command\ApplicationManifestValidateCommand;
use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\ServiceInterface\ApplicationManifestServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ApplicationManifestAndImportMapCommandTest extends TestCase
{
    public function testManifestValidateCommandNormalizesPayload(): void
    {
        $service = $this->createMock(ApplicationManifestServiceInterface::class);
        $service->expects(self::once())
            ->method('normalizeManifestPayload')
            ->with(self::callback(static function (ApplicationManifestDTO $data): bool {
                return 'demo.manifest' === $data->identifier
                    && "listing\nreporting" === $data->capabilities;
            }))
            ->willReturn([
                'identifier' => 'demo.manifest',
                'capabilities' => ['listing', 'reporting'],
            ]);

        $tester = new CommandTester(new ApplicationManifestValidateCommand($service));
        $status = $tester->execute([
            'identifier' => 'demo.manifest',
            'capabilities' => 'listing,reporting',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('Manifest payload normalized.', $tester->getDisplay());
        self::assertStringContainsString('"identifier": "demo.manifest"', $tester->getDisplay());
        self::assertStringContainsString('"listing"', $tester->getDisplay());
        self::assertStringContainsString('"reporting"', $tester->getDisplay());
    }

    public function testManifestValidateCommandRejectsEmptyIdentifier(): void
    {
        $service = $this->createMock(ApplicationManifestServiceInterface::class);
        $service->expects(self::never())->method('normalizeManifestPayload');

        $tester = new CommandTester(new ApplicationManifestValidateCommand($service));
        $status = $tester->execute([
            'identifier' => '',
            'capabilities' => 'listing,reporting',
        ]);

        self::assertSame(Command::INVALID, $status);
        self::assertStringContainsString('Manifest arguments are invalid.', $tester->getDisplay());
    }

    public function testManifestValidateCommandUsesDefaultCapabilities(): void
    {
        $service = $this->createMock(ApplicationManifestServiceInterface::class);
        $service->expects(self::once())
            ->method('normalizeManifestPayload')
            ->with(self::callback(static fn (ApplicationManifestDTO $data): bool => "listing\nreporting" === $data->capabilities))
            ->willReturn(['identifier' => 'default.manifest']);

        $tester = new CommandTester(new ApplicationManifestValidateCommand($service));
        $status = $tester->execute(['identifier' => 'default.manifest']);

        self::assertSame(Command::SUCCESS, $status);
    }

    public function testImportMapAuditReportsIntentionalEmptyRuntime(): void
    {
        $tester = new CommandTester(new ApplicationImportMapAuditCommand());
        $status = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString(
            'No importmap assets are defined in the active Applicating runtime.',
            $tester->getDisplay(),
        );
    }
}
