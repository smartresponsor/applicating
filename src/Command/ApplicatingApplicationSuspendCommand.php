<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Repository\ApplicationRepository;
use App\Application\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:suspend', description: 'Suspend an application publication lifecycle')]
final class ApplicatingApplicationSuspendCommand extends Command
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('slug', InputArgument::REQUIRED, 'Application slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slug = $input->getArgument('slug');
        if (!is_string($slug) || '' === $slug) {
            $output->writeln('<error>Application slug must be a non-empty string.</error>');

            return Command::INVALID;
        }

        $application = $this->applicationRepository->findOneBy(['slug' => $slug]);
        if (null === $application) {
            $output->writeln('<error>Application not found.</error>');

            return Command::FAILURE;
        }

        $this->applicationLifecycleService->suspendApplication($application);
        $output->writeln(sprintf('<info>Suspended %s.</info>', $application->getSlug()));

        return Command::SUCCESS;
    }
}
