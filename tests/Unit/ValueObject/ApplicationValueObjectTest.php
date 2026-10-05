<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\ValueObject;

use App\Applicating\ValueObject\ApplicationManifestIdentifier;
use App\Applicating\ValueObject\ApplicationSlug;
use App\Applicating\ValueObject\ApplicationVersion;
use PHPUnit\Framework\TestCase;

final class ApplicationValueObjectTest extends TestCase
{
    public function testValidValuesRoundTrip(): void
    {
        $slug = new ApplicationSlug('demo-application');
        $version = new ApplicationVersion('1.2.3-rc.1');
        $identifier = new ApplicationManifestIdentifier('io.applicating.demo');

        self::assertSame('demo-application', $slug->toString());
        self::assertSame('demo-application', (string) $slug);
        self::assertSame('1.2.3-rc.1', $version->toString());
        self::assertSame('io.applicating.demo', $identifier->toString());
    }

    public function testInvalidSlugIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application slug must use lowercase kebab-case.');

        new ApplicationSlug('Demo Application');
    }

    public function testInvalidVersionIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application version must use semver-like format.');

        new ApplicationVersion('1.2');
    }

    public function testInvalidManifestIdentifierIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manifest identifier must use reverse-domain notation.');

        new ApplicationManifestIdentifier('demo');
    }
}
