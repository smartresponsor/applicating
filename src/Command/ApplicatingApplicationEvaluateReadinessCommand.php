<?php

declare(strict_types=1);

namespace App\Applicating\Command;

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
        [$minScore, $profile] = $this->evaluationOptions($input);
        $root = dirname(__DIR__, 2);
        $script = $root.'/tools/evaluation/run_readiness_evaluation.php';

        $_ENV['APP_READINESS_EVAL_MIN_SCORE'] = (string) $minScore;
        $_ENV['APP_READINESS_EVAL_PROFILE'] = $profile;

        require $script;

        [$summary, $policy, $delta] = $this->reportSections($root.'/report/evaluation/application_readiness_evaluation.json');
        $weightedScore = $this->numericValue($summary, 'weightedScore');
        $weightedScoreDelta = $this->numericValue($delta, 'weightedScoreDelta');
        $policyProfile = $this->stringValue($policy, 'profile', 'unknown');
        $shouldFail = true === ($policy['shouldFail'] ?? false);
        $failedByRegression = true === ($policy['failedByRegression'] ?? false);

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

    /** @return array{float, string} */
    private function evaluationOptions(InputInterface $input): array
    {
        $rawMinScore = $input->getOption('min-score');
        $rawProfile = $input->getOption('eval-profile');

        return [
            max(0.0, min(1.0, is_scalar($rawMinScore) ? (float) $rawMinScore : 1.0)),
            is_scalar($rawProfile) ? (string) $rawProfile : 'strict',
        ];
    }

    /** @return array{array<string, mixed>, array<string, mixed>, array<string, mixed>} */
    private function reportSections(string $reportPath): array
    {
        $decoded = json_decode((string) file_get_contents($reportPath), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Readiness evaluation report is not a valid JSON object.');
        }

        return [
            $this->stringKeyedArray($decoded['summary'] ?? null),
            $this->stringKeyedArray($decoded['policy'] ?? null),
            $this->stringKeyedArray($decoded['delta'] ?? null),
        ];
    }

    /** @return array<string, mixed> */
    private function stringKeyedArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $data */
    private function numericValue(array $data, string $key): float
    {
        return isset($data[$key]) && is_numeric($data[$key]) ? (float) $data[$key] : 0.0;
    }

    /** @param array<string, mixed> $data */
    private function stringValue(array $data, string $key, string $fallback): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : $fallback;
    }
}
