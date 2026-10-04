<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the shared slug argument and application lookup contract for lifecycle commands.
 */
abstract class ApplicationSlugCommand extends Command
{
    public function __construct(private readonly ApplicationRepository $applicationRepository)
    {
        parent::__construct();
    }

    /**
     * Declares the canonical application slug argument used by derived lifecycle commands.
     */
    protected function configureSlugArgument(): void
    {
        $this->addArgument('slug', InputArgument::REQUIRED, 'Application slug');
    }

    /**
     * Resolves a non-empty slug to the canonical application entity or reports lookup failure.
     */
    protected function resolveApplication(InputInterface $input, OutputInterface $output): ?ApplicationEntity
    {
        $slug = $input->getArgument('slug');
        if (!is_string($slug) || '' === $slug) {
            $output->writeln('<error>Application slug must be a non-empty string.</error>');

            return null;
        }

        $application = $this->applicationRepository->findOneBySlug($slug);
        if (!$application instanceof ApplicationEntity) {
            $output->writeln('<error>Application not found.</error>');

            return null;
        }

        return $application;
    }
}
