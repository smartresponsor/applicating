<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Application;
use App\Entity\ApplicationRelease;
use PHPUnit\Framework\TestCase;

final class ApplicationReleaseBehaviorTest extends TestCase
{
    public function testConstructorKeepsReleaseMetadata(): void
    {
        $application = new Application(
            'Release Demo Application',
            'release-demo-application',
            'applicating/release-demo-application',
            'Applicating Labs',
            'Release listing summary'
        );

        $release = new ApplicationRelease(
            $application,
            '1.2.3',
            'stable',
            hash('sha256', 'release-demo'),
            'https://downloads.example.test/release-demo/1.2.3.zip',
            'Release notes'
        );

        self::assertSame($application, $release->getApplication());
        self::assertSame('1.2.3', $release->getVersion());
        self::assertSame('stable', $release->getChannel());
        self::assertSame('https://downloads.example.test/release-demo/1.2.3.zip', $release->getDownloadUrl());
        self::assertSame('Release notes', $release->getReleaseNotes());
        self::assertSame('draft', $release->getPublicationState()->value);
        self::assertNotNull($release->getCreatedAt());
        self::assertNull($release->getPublishedAt());
    }

    public function testPublishTransitionsReleaseState(): void
    {
        $application = new Application(
            'Release Demo Application',
            'release-demo-application',
            'applicating/release-demo-application',
            'Applicating Labs',
            'Release listing summary'
        );

        $release = new ApplicationRelease(
            $application,
            '1.2.3',
            'stable',
            hash('sha256', 'release-demo'),
            'https://downloads.example.test/release-demo/1.2.3.zip',
            'Release notes'
        );

        $release->publish();

        self::assertSame('published', $release->getPublicationState()->value);
        self::assertNotNull($release->getPublishedAt());
    }
}
