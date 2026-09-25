<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationTenantDiagnosticsDTO;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;

interface ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(ApplicationTenantAssignmentEntity $tenantApplication): ApplicationTenantDiagnosticsDTO;
}
