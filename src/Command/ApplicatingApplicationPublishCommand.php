<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:publish', description: 'Publish an application with its latest release')]
final class ApplicatingApplicationPublishCommand extends Command
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

        $latestRelease = $application->getReleases()->first();
        if (false === $latestRelease) {
            $output->writeln('<error>Application has no release to publish.</error>');

            return Command::FAILURE;
        }

        try {
            $this->applicationLifecycleService->publishApplication($application, $latestRelease);
        } catch (\LogicException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Published %s with release %s.</info>', $application->getSlug(), $latestRelease->getVersion()));

        return Command::SUCCESS;
    }
}
