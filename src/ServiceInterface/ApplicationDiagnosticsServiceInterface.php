<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\TenantApplicationDiagnostics;
use App\Applicating\Entity\TenantApplication;

interface ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): TenantApplicationDiagnostics;
}
