<?php

declare(strict_types=1);

namespace App\Applicating\Command;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ([
            'DELETE FROM App\\Applicating\\Entity\\TenantApplication tenantApplication',
            'DELETE FROM App\\Applicating\\Entity\\ApplicationManifest applicationManifest',
            'DELETE FROM App\\Applicating\\Entity\\ApplicationRelease applicationRelease',
            'DELETE FROM App\\Applicating\\Entity\\Application applicationListing',
        ] as $dql) {
            $this->entityManager->createQuery($dql)->execute();
        }

        $output->writeln('<info>Application demo data reset.</info>');

        return Command::SUCCESS;
    }
}
