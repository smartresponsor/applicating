<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:publish', description: 'Publish an application with its latest release')]
/**
 * Publishes an application through the lifecycle service using its latest available release.
 */
final class ApplicationPublishCommand extends ApplicationSlugCommand
{
    public function __construct(
        ApplicationRepository $applicationRepository,
        private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ) {
        parent::__construct($applicationRepository);
    }

    /**
     * Declares the canonical application slug argument shared by lifecycle commands.
     */
    protected function configure(): void
    {
        $this->configureSlugArgument();
    }

    /**
     * Resolves the application and publishes its latest release when lifecycle policy permits it.
     */
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
