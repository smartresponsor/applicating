<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\TenantApplicationDiagnostics;
use App\Entity\TenantApplication;

interface ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): TenantApplicationDiagnostics;
}
