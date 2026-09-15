<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Entity\Application;
use App\Applicating\Repository\ApplicationRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractApplicatingApplicationSlugCommand extends Command
{
    public function __construct(private readonly ApplicationRepository $applicationRepository)
    {
        parent::__construct();
    }

    protected function configureSlugArgument(): void
    {
        $this->addArgument('slug', InputArgument::REQUIRED, 'Application slug');
    }

    protected function resolveApplication(InputInterface $input, OutputInterface $output): ?Application
    {
        $slug = $input->getArgument('slug');
        if (!is_string($slug) || '' === $slug) {
            $output->writeln('<error>Application slug must be a non-empty string.</error>');

            return null;
        }

        $application = $this->applicationRepository->findOneBySlug($slug);
        if (!$application instanceof Application) {
            $output->writeln('<error>Application not found.</error>');

            return null;
        }

        return $application;
    }
}
