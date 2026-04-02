<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\ApplicationReadinessServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:readiness', description: 'Show readiness diagnostics for an application')]
final class ApplicatingApplicationReadinessCommand extends Command
{
    public function __construct(
        private readonly ApplicationReadinessServiceInterface $applicationReadinessService,
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

        $readiness = $this->applicationReadinessService->buildReadiness($slug);

        $output->writeln(sprintf('<info>Application readiness for %s</info>', $slug));
        $output->writeln(sprintf('canPublish: %s', $readiness->canPublish ? 'true' : 'false'));

        if ([] !== $readiness->blockingReasons) {
            $output->writeln('blockingReasons:');
            foreach ($readiness->blockingReasons as $reason) {
                $output->writeln(sprintf(' - %s', $reason));
            }
        }

        if ([] !== $readiness->warnings) {
            $output->writeln('warnings:');
            foreach ($readiness->warnings as $warning) {
                $output->writeln(sprintf(' - %s', $warning));
            }
        }

        return $readiness->canPublish ? Command::SUCCESS : Command::FAILURE;
    }
}
