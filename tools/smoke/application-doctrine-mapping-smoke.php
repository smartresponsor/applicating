<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

require dirname(__DIR__, 2).'/config/bootstrap.php';

$kernel = new Kernel('test', true);
$kernel->boot();
/** @var ManagerRegistry $doctrine */
$doctrine = $kernel->getContainer()->get('doctrine');
/** @var EntityManagerInterface $entityManager */
$entityManager = $doctrine->getManager();
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

if ([] === $metadata) {
    fwrite(STDERR, "No Doctrine metadata found.\n");
    exit(1);
}

fwrite(STDOUT, sprintf("Application doctrine mapping smoke passed (%d entities).\n", count($metadata)));
