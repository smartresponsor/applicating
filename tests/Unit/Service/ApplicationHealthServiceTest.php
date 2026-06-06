<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\Entity\Application;
use App\Applicating\Service\ApplicationHealthService;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class ApplicationHealthServiceTest extends TestCase
{
    public function testBuildHealthReturnsExpectedLivenessPayload(): void
    {
        $service = new ApplicationHealthService($this->createEntityManager([Application::class]));

        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildHealth();

        self::assertSame('ok', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }

    public function testBuildReadinessReturnsNotReadyWhenDatabaseFails(): void
    {
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query
            ->expects(self::once())
            ->method('getSingleScalarResult')
            ->willThrowException(new \RuntimeException('forced-db-failure'));

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($queryBuilder);

        $service = new ApplicationHealthService($entityManager);
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildReadiness();

        self::assertSame('not_ready', $payload['status']);
        self::assertSame('down', $payload['checks']['database']['status']);
        self::assertSame('forced-db-failure', $payload['checks']['database']['message']);
    }

    /**
     * @param list<class-string> $entityClasses
     */
    private function createEntityManager(array $entityClasses): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfig([dirname(__DIR__, 3).'/src/Entity'], true);
        $config->enableNativeLazyObjects(true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], new Configuration());
        $em = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($em);
        $metadata = [];
        foreach ($entityClasses as $entityClass) {
            $metadata[] = $em->getClassMetadata($entityClass);
        }
        $schemaTool->createSchema($metadata);

        return $em;
    }
}
