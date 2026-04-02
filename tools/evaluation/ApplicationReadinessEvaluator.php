<?php

declare(strict_types=1);

use App\DTO\Application\ApplicationReadiness;

final class ApplicationReadinessEvaluator
{
    /**
     * @param array{name: string, slug: string, expected: array{canPublish: bool, blockingReasons?: list<string>}} $scenario
     * @return array{passed: bool, mismatches: list<string>}
     */
    public function evaluate(array $scenario, ApplicationReadiness $readiness): array
    {
        $mismatches = [];
        $expected = $scenario['expected'];

        if ($readiness->canPublish !== $expected['canPublish']) {
            $mismatches[] = sprintf(
                'Expected canPublish=%s, got %s.',
                $expected['canPublish'] ? 'true' : 'false',
                $readiness->canPublish ? 'true' : 'false'
            );
        }

        foreach ($expected['blockingReasons'] ?? [] as $reason) {
            if (!in_array($reason, $readiness->blockingReasons, true)) {
                $mismatches[] = sprintf('Missing blocking reason: %s', $reason);
            }
        }

        return [
            'passed' => [] === $mismatches,
            'mismatches' => $mismatches,
        ];
    }
}
