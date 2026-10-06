<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;

/**
 * Defines persistence operations for tenant-to-application lifecycle assignments.
 */
interface ApplicationTenantAssignmentRepositoryInterface
{
    /**
     * Return assignments for one tenant ordered by the canonical recency rule.
     *
     * @return list<ApplicationTenantAssignmentEntity>
     */
    public function findForTenant(string $tenantKey): array;

    /**
     * Resolve one assignment by tenant key and application slug.
     */
    public function findOneForTenantAndApplication(string $tenantKey, string $applicationSlug): ?ApplicationTenantAssignmentEntity;

    /**
     * Resolve one assignment by tenant key and application entity.
     */
    public function findOneForTenantAndApplicationEntity(string $tenantKey, ApplicationEntity $application): ?ApplicationTenantAssignmentEntity;

    /**
     * Count every persisted tenant assignment.
     */
    public function countAllAssignments(): int;

    /**
     * Count assignments currently enabled for tenant access.
     */
    public function countEnabledAssignments(): int;

    /**
     * Count assignments whose billing lifecycle is active.
     */
    public function countBillingActiveAssignments(): int;

    /**
     * Persist an assignment and make the change durable immediately.
     */
    public function save(ApplicationTenantAssignmentEntity $assignment): void;
}
