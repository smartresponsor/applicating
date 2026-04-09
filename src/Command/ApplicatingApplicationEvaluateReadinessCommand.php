<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:evaluate-readiness', description: 'Run readiness evaluation scenarios')]
final class ApplicatingApplicationEvaluateReadinessCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('min-score', null, InputOption::VALUE_REQUIRED, 'Minimum passing score between 0 and 1.', '1.0')
            ->addOption('eval-profile', null, InputOption::VALUE_REQUIRED, 'Evaluation profile: strict|soft|dev', 'strict');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawMinScore = $input->getOption('min-score');
        $rawProfile = $input->getOption('eval-profile');

        $minScore = max(0.0, min(1.0, is_scalar($rawMinScore) ? (float) $rawMinScore : 1.0));
        $profile = is_scalar($rawProfile) ? (string) $rawProfile : 'strict';

        $root = dirname(__DIR__, 2);
        $script = $root . '/tools/evaluation/run_readiness_evaluation.php';

        $_ENV['APP_READINESS_EVAL_MIN_SCORE'] = (string) $minScore;
        $_ENV['APP_READINESS_EVAL_PROFILE'] = $profile;

        require $script;

        $reportPath = $root . '/report/evaluation/application_readiness_evaluation.json';
        $decoded = json_decode((string) file_get_contents($reportPath), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Readiness evaluation report is not a valid JSON object.');
        }

        $summary = is_array($decoded['summary'] ?? null) ? $decoded['summary'] : [];
        $policy = is_array($decoded['policy'] ?? null) ? $decoded['policy'] : [];
        $delta = is_array($decoded['delta'] ?? null) ? $decoded['delta'] : [];

        $weightedScore = isset($summary['weightedScore']) && is_numeric($summary['weightedScore']) ? (float) $summary['weightedScore'] : 0.0;
        $weightedScoreDelta = isset($delta['weightedScoreDelta']) && is_numeric($delta['weightedScoreDelta']) ? (float) $delta['weightedScoreDelta'] : 0.0;
        $policyProfile = is_string($policy['profile'] ?? null) ? $policy['profile'] : 'unknown';
        $shouldFail = ($policy['shouldFail'] ?? false) === true;
        $failedByRegression = ($policy['failedByRegression'] ?? false) === true;

        $output->writeln(sprintf('<info>Profile:</info> %s', $policyProfile));
        $output->writeln(sprintf('<info>Evaluation total:</info> %d', isset($summary['total']) && is_int($summary['total']) ? $summary['total'] : 0));
        $output->writeln(sprintf('<info>Score:</info> %.4f', $weightedScore));
        $output->writeln(sprintf('<info>Delta:</info> %.4f', $weightedScoreDelta));
        $output->writeln(sprintf('<info>Should fail:</info> %s', $shouldFail ? 'true' : 'false'));

        if ($failedByRegression) {
            $output->writeln('<error>Regression detected</error>');
        }

        return $shouldFail ? Command::FAILURE : Command::SUCCESS;
    }
}
