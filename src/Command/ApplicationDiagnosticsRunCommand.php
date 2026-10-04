<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationTenantAssignmentRepository;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:diagnostics:run', description: 'Run diagnostics for tenant application assignments')]
/**
 * Emits JSON diagnostics for application assignments, optionally scoped by tenant key.
 */
final class ApplicationDiagnosticsRunCommand extends Command
{
    public function __construct(
        private readonly ApplicationTenantAssignmentRepository $tenantApplicationRepository,
        private readonly ApplicationDiagnosticsServiceInterface $applicationDiagnosticsService,
    ) {
        parent::__construct();
    }

    /**
     * Declares the optional tenant selector used to narrow diagnostic output.
     */
    protected function configure(): void
    {
        $this->addArgument('tenantKey', InputArgument::OPTIONAL, 'Filter by tenant key');
    }

    /**
     * Serializes each selected assignment diagnostic as one JSON output line.
     *
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantKey = $input->getArgument('tenantKey');
        $assignments = is_string($tenantKey) && '' !== $tenantKey
            ? $this->tenantApplicationRepository->findForTenant($tenantKey)
            : $this->tenantApplicationRepository->findAll();

        foreach ($assignments as $assignment) {
            $output->writeln(json_encode(
                $this->applicationDiagnosticsService->buildTenantDiagnostics($assignment)->toArray(),
                JSON_THROW_ON_ERROR
            ));
        }

        return Command::SUCCESS;
    }
}
