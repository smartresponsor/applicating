<?php

declare(strict_types=1);

namespace App\Component\Product\FeatureFlags;

final class EnvFeatureFlagProvider implements FeatureFlagProviderInterface
{
    public function isEnabled(string $flag): bool
    {
        $key = 'FF_'.strtoupper(str_replace('-', '_', $flag));
        $val = getenv($key);
        if (false === $val) {
            return false;
        }

        return in_array(strtolower((string) $val), ['1', 'true', 'yes', 'on'], true);
    }
}
