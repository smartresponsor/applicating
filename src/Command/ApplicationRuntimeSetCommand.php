<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\ServiceInterface\ApplicationRuntimeAssignmentServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:runtime:set', description: 'Set application runtime mode for one environment')]
/** Changes the runtime-mode assignment for one application environment through the lifecycle service boundary. */
final class ApplicationRuntimeSetCommand extends Command
{
    public function __construct(private readonly ApplicationRuntimeAssignmentServiceInterface $runtimeAssignmentService)
    {
        parent::__construct();
    }

    /** Defines the application, environment, and canonical runtime-mode arguments required by the operation. */
    protected function configure(): void
    {
        $this
            ->addArgument('applicationSlug', InputArgument::REQUIRED, 'Application slug')
            ->addArgument('environment', InputArgument::REQUIRED, 'Application environment')
            ->addArgument('mode', InputArgument::REQUIRED, 'host_shared or custom_domain');
    }

    /** Validates runtime-mode input, applies the assignment, and reports the resulting application environment state. */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $applicationSlug = $input->getArgument('applicationSlug');
        $environment = $input->getArgument('environment');
        $mode = $input->getArgument('mode');
        if (!is_string($applicationSlug) || !is_string($environment) || !is_string($mode)) {
            return Command::INVALID;
        }

        try {
            $runtimeMode = ApplicationRuntimeMode::from($mode);
            $assignment = $this->runtimeAssignmentService->setMode($applicationSlug, $environment, $runtimeMode);
        } catch (\ValueError|\InvalidArgumentException|\DomainException $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>%s:%s => %s</info>', $assignment->getApplication()->getSlug(), $assignment->getEnvironment(), $assignment->getRuntimeMode()->value));

        return Command::SUCCESS;
    }
}
