<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationFixtureRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:demo:reset', description: 'Reset application demo data')]
final class ApplicationDemoResetCommand extends Command
{
    public function __construct(private readonly ApplicationFixtureRepository $applicationFixtureRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->applicationFixtureRepository->resetDemoData();

        $output->writeln('<info>Application demo data reset.</info>');

        return Command::SUCCESS;
    }
}
