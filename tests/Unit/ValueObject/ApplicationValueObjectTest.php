<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\ApplicationManifestIdentifier;
use App\ValueObject\ApplicationSlug;
use App\ValueObject\ApplicationVersion;
use PHPUnit\Framework\TestCase;

final class ApplicationValueObjectTest extends TestCase
{
    public function testApplicationSlugAcceptsLowercaseKebabCase(): void
    {
        $slug = new ApplicationSlug('demo-application');

        self::assertSame('demo-application', $slug->toString());
        self::assertSame('demo-application', (string) $slug);
    }

    public function testApplicationSlugRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application slug must use lowercase kebab-case.');

        new ApplicationSlug('Demo Application');
    }

    public function testApplicationVersionAcceptsSemverLikeFormat(): void
    {
        $version = new ApplicationVersion('1.2.3-beta.1');

        self::assertSame('1.2.3-beta.1', $version->toString());
    }

    public function testApplicationVersionRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application version must use semver-like format.');

        new ApplicationVersion('1.2');
    }

    public function testApplicationManifestIdentifierAcceptsReverseDomainNotation(): void
    {
        $identifier = new ApplicationManifestIdentifier('io.applicating.demo.application');

        self::assertSame('io.applicating.demo.application', $identifier->toString());
    }

    public function testApplicationManifestIdentifierRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manifest identifier must use reverse-domain notation.');

        new ApplicationManifestIdentifier('bad identifier');
    }
}
