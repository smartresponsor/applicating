<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationTenantAssignmentRepository;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:tenant:toggle', description: 'Enable or disable an assigned tenant application')]
/**
 * Enables or disables one existing tenant application assignment through lifecycle policy.
 */
final class ApplicationTenantToggleCommand extends Command
{
    public function __construct(
        private readonly ApplicationTenantAssignmentRepository $tenantApplicationRepository,
        private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ) {
        parent::__construct();
    }

    /**
     * Declares the tenant, application, and disable switch required by the lifecycle operation.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('tenantKey', InputArgument::REQUIRED, 'Tenant key')
            ->addArgument('applicationSlug', InputArgument::REQUIRED, 'Application slug')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable the tenant application instead of enabling it');
    }

    /**
     * Applies the requested enabled state and reports the resulting installation status.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantKey = $input->getArgument('tenantKey');
        $applicationSlug = $input->getArgument('applicationSlug');

        if (!is_string($tenantKey) || '' === $tenantKey || !is_string($applicationSlug) || '' === $applicationSlug) {
            $output->writeln('<error>Tenant key and application slug are required.</error>');

            return Command::INVALID;
        }

        $tenantApplication = $this->tenantApplicationRepository->findOneForTenantAndApplication($tenantKey, $applicationSlug);
        if (null === $tenantApplication) {
            $output->writeln('<error>Tenant application assignment not found.</error>');

            return Command::FAILURE;
        }

        $enabled = !$input->getOption('disable');
        $this->applicationLifecycleService->toggleTenantApplication($tenantApplication, $enabled);

        $output->writeln(sprintf(
            '<info>%s:%s => %s (%s)</info>',
            $tenantApplication->getTenantKey(),
            $tenantApplication->getApplication()->getSlug(),
            $tenantApplication->isEnabled() ? 'enabled' : 'disabled',
            $tenantApplication->getInstallationState()->value
        ));

        return Command::SUCCESS;
    }
}
