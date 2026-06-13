<?php

declare(strict_types=1);

namespace App\Applicating\Lifecycle;

/**
 * Lifecycle guard for application.
 *
 * The policy is intentionally framework-free: entities/services can call it
 * without introducing cross-component Doctrine dependencies.
 */
final class ApplicationLifecyclePolicy
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['review', 'active', 'deprecated'],
        'review' => ['active', 'rejected', 'draft'],
        'active' => ['suspended', 'deprecated', 'archived'],
        'suspended' => ['active', 'deprecated', 'archived'],
        'deprecated' => ['archived'],
        'rejected' => ['draft', 'archived'],
        'archived' => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        if ($from === $to) {
            return true;
        }

        return \in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function assertCanTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new \DomainException(sprintf('Invalid application lifecycle transition from "%s" to "%s".', $from, $to));
        }
    }

    /** @return list<string> */
    public function allowedNextStatuses(string $from): array
    {
        return self::TRANSITIONS[self::normalize($from)] ?? [];
    }

    private static function normalize(string $status): string
    {
        return strtolower(trim($status));
    }
}
