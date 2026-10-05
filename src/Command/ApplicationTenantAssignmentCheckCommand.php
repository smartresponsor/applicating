<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationTenantAssignmentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:tenant:assignment:check', description: 'Check tenant assignment consistency for applications')]
/**
 * Reports persisted tenant-to-application assignment state for operational consistency checks.
 */
final class ApplicationTenantAssignmentCheckCommand extends Command
{
    public function __construct(private readonly ApplicationTenantAssignmentRepository $tenantApplicationRepository)
    {
        parent::__construct();
    }

    /**
     * Declares the optional tenant key used to scope assignment inspection.
     */
    protected function configure(): void
    {
        $this->addArgument('tenantKey', InputArgument::OPTIONAL, 'Tenant key');
    }

    /**
     * Lists the selected assignments with tenant, application, version, and installation state.
     */
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
