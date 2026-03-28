<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\TenantApplicationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:tenant:assignment:check', description: 'Check tenant assignment consistency for applications')]
final class ApplicatingTenantAssignmentCheckCommand extends Command
{
    public function __construct(private readonly TenantApplicationRepository $tenantApplicationRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tenantKey', InputArgument::OPTIONAL, 'Tenant key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantKey = $input->getArgument('tenantKey');
        $assignments = is_string($tenantKey) && '' !== $tenantKey
            ? $this->tenantApplicationRepository->findForTenant($tenantKey)
            : $this->tenantApplicationRepository->findAll();

        foreach ($assignments as $assignment) {
            $output->writeln(sprintf(
                '%s:%s:%s:%s',
                $assignment->getTenantKey(),
                $assignment->getApplication()->getSlug(),
                $assignment->getInstalledVersion(),
                $assignment->getInstallationState()->value
            ));
        }

        return Command::SUCCESS;
    }
}
