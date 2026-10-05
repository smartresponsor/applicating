<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Enum\ApplicationPublicationState;
use PHPUnit\Framework\TestCase;

final class ApplicationReleaseEntityTest extends TestCase
{
    public function testReleaseExposesDraftStateAndMetadata(): void
    {
        $application = $this->application();
        $release = new ApplicationReleaseEntity(
            $application,
            '1.2.3',
            'stable',
            'abc123',
            'https://example.test/releases/1.2.3.zip',
            'Release notes.',
        );

        self::assertNull($release->getId());
        self::assertSame($application, $release->getApplication());
        self::assertSame('1.2.3', $release->getVersion());
        self::assertSame('stable', $release->getChannel());
        self::assertSame('abc123', $release->getChecksum());
        self::assertSame('https://example.test/releases/1.2.3.zip', $release->getDownloadUrl());
        self::assertSame('Release notes.', $release->getReleaseNotes());
        self::assertSame(ApplicationPublicationState::Draft, $release->getPublicationState());
        self::assertInstanceOf(\DateTimeImmutable::class, $release->getCreatedAt());
        self::assertNull($release->getPublishedAt());
    }

    public function testPublishTransitionsStateAndTimestamp(): void
    {
        $release = new ApplicationReleaseEntity(
            $this->application(),
            '2.0.0',
            'stable',
            'def456',
            'https://example.test/releases/2.0.0.zip',
            'Major release.',
        );

        $release->publish();

        self::assertSame(ApplicationPublicationState::Published, $release->getPublicationState());
        self::assertInstanceOf(\DateTimeImmutable::class, $release->getPublishedAt());
    }

    private function application(): ApplicationEntity
    {
        return new ApplicationEntity(
            'Demo Application',
            'demo-application',
            'demo/application',
            'Demo Developer',
            'Demo application summary.',
        );
    }
}
