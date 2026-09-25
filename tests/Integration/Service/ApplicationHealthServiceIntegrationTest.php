<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Integration\Service;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\Service\ApplicationHealthService;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class ApplicationHealthServiceIntegrationTest extends TestCase
{
    public function testBuildReadinessReturnsReadyWithConfiguredConnection(): void
    {
        $entityManager = $this->createEntityManager([ApplicationEntity::class]);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with(ApplicationEntity::class)->willReturn($entityManager);
        $repository = new ApplicationRepository($registry);
        $service = new ApplicationHealthService($repository);
        $payload = $service->buildReadiness();

        self::assertSame('ready', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
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
        $entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = [];
        foreach ($entityClasses as $entityClass) {
            $metadata[] = $entityManager->getClassMetadata($entityClass);
        }
        $schemaTool->createSchema($metadata);

        return $entityManager;
    }
}
