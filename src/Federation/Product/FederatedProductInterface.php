<?php

declare(strict_types=1);

namespace App\Federation\Product;

interface FederatedProductInterface
{
    public function getFederationId(): string;

    public function getTenantId(): string;

    public function getRegion(): ?string;

    public function getScope(): array;
}
