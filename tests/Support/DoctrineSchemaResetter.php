<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

final class DoctrineSchemaResetter
{
    public static function reset(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if ([] === $metadata) {
            return;
        }

        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $schemaManager = $connection->createSchemaManager();

        foreach ($schemaManager->listTableNames() as $tableName) {
            $sql = $platform->getDropTableSQL($tableName);
            $connection->executeStatement($sql);
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($metadata);
    }
}
