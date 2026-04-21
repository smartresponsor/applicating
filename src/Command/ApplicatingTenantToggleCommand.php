<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\Repository\TenantApplicationRepository;
use App\Application\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:tenant:toggle', description: 'Enable or disable an assigned tenant application')]
final class ApplicatingTenantToggleCommand extends Command
{
    public function __construct(
        private readonly TenantApplicationRepository $tenantApplicationRepository,
        private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tenantKey', InputArgument::REQUIRED, 'Tenant key')
            ->addArgument('applicationSlug', InputArgument::REQUIRED, 'Application slug')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Disable the tenant application instead of enabling it');
    }

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
