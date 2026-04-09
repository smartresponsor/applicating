<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\ApplicationReportServiceInterface;
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
            $output->writeln(sprintf('%s=%s', $key, is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR)));
        }

        return Command::SUCCESS;
    }
}
