<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\TenantApplication;

interface TenantApplicationRepositoryInterface
{
    /** @return list<TenantApplication> */
    public function findForTenant(string $tenantKey): array;

    public function findOneForTenantAndApplication(string $tenantKey, string $applicationSlug): ?TenantApplication;

    public function findOneForTenantAndApplicationEntity(string $tenantKey, Application $application): ?TenantApplication;

    public function countAllAssignments(): int;

    public function countEnabledAssignments(): int;
}
