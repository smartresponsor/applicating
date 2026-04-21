<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:demo:reset', description: 'Reset application demo data')]
final class ApplicatingDemoResetCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();
        foreach (['tenant_application', 'application_manifest', 'application_release', 'application_listing'] as $table) {
            $connection->executeStatement(sprintf('DELETE FROM %s', $table));
        }

        $output->writeln('<info>Application demo data reset.</info>');

        return Command::SUCCESS;
    }
}
