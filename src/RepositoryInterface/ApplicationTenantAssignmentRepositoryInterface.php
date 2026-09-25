<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;

interface ApplicationTenantAssignmentRepositoryInterface
{
    /** @return list<ApplicationTenantAssignmentEntity> */
    public function findForTenant(string $tenantKey): array;

    public function findOneForTenantAndApplication(string $tenantKey, string $applicationSlug): ?ApplicationTenantAssignmentEntity;

    public function findOneForTenantAndApplicationEntity(string $tenantKey, ApplicationEntity $application): ?ApplicationTenantAssignmentEntity;

    public function countAllAssignments(): int;

    public function countEnabledAssignments(): int;

    public function save(ApplicationTenantAssignmentEntity $assignment): void;
}
