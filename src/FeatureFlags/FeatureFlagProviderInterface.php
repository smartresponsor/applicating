<?php

declare(strict_types=1);

namespace App\Component\Product\FeatureFlags;

interface FeatureFlagProviderInterface
{
    public function isEnabled(string $flag): bool;
}
