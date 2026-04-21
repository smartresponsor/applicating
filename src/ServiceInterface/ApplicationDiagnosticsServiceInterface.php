<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\TenantApplicationDiagnostics;
use App\Application\Entity\TenantApplication;

interface ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): TenantApplicationDiagnostics;
}
