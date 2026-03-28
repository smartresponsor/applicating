<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\TenantApplication;

interface ApplicationDiagnosticsServiceInterface
{
    /** @return array<string, mixed> */
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): array;
}
