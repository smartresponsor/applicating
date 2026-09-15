<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:report:summary', description: 'Print application lifecycle summary report')]
final class ApplicatingReportSummaryCommand extends Command
{
    public function __construct(private readonly ApplicationReportServiceInterface $applicationReportService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $summary = $this->applicationReportService->buildSummary()->toArray();
        foreach ($summary as $key => $value) {
            $output->writeln(sprintf('%s=%d', $key, $value));
        }

        return Command::SUCCESS;
    }
}
