<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationAccessBoundaryTest extends WebTestCase
{
    public function testLoginSurfaceIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('protectedSurfaceProvider')]
    public function testProtectedApplicationSurfaceRejectsAnonymousRequest(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseStatusCodeSame(403);
    }

    /** @return iterable<string, array{string}> */
    public static function protectedSurfaceProvider(): iterable
    {
        yield 'application admin index' => ['/admin/applications'];
        yield 'application API index' => ['/api/applicating/application'];
        yield 'application API report' => ['/api/applicating/application/report'];
        yield 'application readiness API' => ['/api/applicating/application/readiness/demo'];
    }
}
