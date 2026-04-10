<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:publish', description: 'Publish an application with its latest release')]
final class ApplicatingApplicationPublishCommand extends AbstractApplicatingApplicationSlugCommand
{
    public function __construct(
        ApplicationRepository $applicationRepository,
        private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ) {
        parent::__construct($applicationRepository);
    }

    protected function configure(): void
    {
        $this->configureSlugArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->resolveApplication($input, $output);
        if (null === $application) {
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
