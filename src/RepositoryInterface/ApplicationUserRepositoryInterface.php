<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationUserEntity;

/**
 * Defines the persistence contract for Applicating security users.
 */
interface ApplicationUserRepositoryInterface
{
    /**
     * Resolve one application user by its security identifier.
     */
    public function findOneByIdentifier(string $userIdentifier): ?ApplicationUserEntity;

    /**
     * Count application users currently marked active.
     */
    public function countActiveUsers(): int;

    /**
     * Persist an application user and make the change durable immediately.
     */
    public function save(ApplicationUserEntity $user): void;
}
