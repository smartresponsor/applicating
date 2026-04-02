<?php

declare(strict_types=1);

final class ApplicationReadinessPolicyEvaluator
{
    public function evaluate(string $profile, array $summary, array $delta): array
    {
        $failedByScore = ($summary['thresholdPassed'] ?? false) !== true;
        $failedByRegression = [] !== ($delta['newFailures'] ?? []);

        return match ($profile) {
            'strict' => [
                'profile' => 'strict',
                'failedByScore' => $failedByScore,
                'failedByRegression' => $failedByRegression,
                'shouldFail' => $failedByScore || $failedByRegression,
            ],
            'soft' => [
                'profile' => 'soft',
                'failedByScore' => $failedByScore,
                'failedByRegression' => $failedByRegression,
                'shouldFail' => $failedByScore,
            ],
            default => [
                'profile' => 'dev',
                'failedByScore' => $failedByScore,
                'failedByRegression' => $failedByRegression,
                'shouldFail' => false,
            ],
        };
    }
}
