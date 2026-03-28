<?php

declare(strict_types=1);

namespace App\Federation\Product;

final class TenantScopeResolver
{
    public function resolve(string $tenantId, ?RegionCode $region, array $scope = []): array
    {
        $regionStr = $region?->value ?? RegionCode::GLOBAL->value;
        ScopeValidator::assertValid($scope);

        return ['tenantId' => $tenantId, 'region' => $regionStr, 'scope' => $scope];
    }
}
