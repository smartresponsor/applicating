<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application\ApplicationEntity;

/**
 * Defines persistence operations for the Applicating root application aggregate.
 */
interface ApplicationRepositoryInterface
{
    /**
     * Resolve an application by its canonical slug identity.
     */
    public function findOneBySlug(string $slug): ?ApplicationEntity;

    /**
     * Return applications in the canonical administrative ordering.
     *
     * @return list<ApplicationEntity>
     */
    public function findOrderedForAdmin(): array;

    /**
     * Count every persisted application aggregate.
     */
    public function countAllApplications(): int;

    /**
     * Count applications currently published for use.
     */
    public function countPublished(): int;

    /**
     * Persist an application aggregate and make the change durable immediately.
     */
    public function save(ApplicationEntity $application): void;
}
