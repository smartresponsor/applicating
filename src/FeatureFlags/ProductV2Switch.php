<?php

declare(strict_types=1);

namespace App\Component\Product\FeatureFlags;

final class ProductV2Switch
{
    public function __construct(private readonly FeatureFlagProviderInterface $ff)
    {
    }

    /** @return array<string,mixed> */
    public function selectFields(): array
    {
        $readV2 = $this->ff->isEnabled('product-v2-read');
        if ($readV2) {
            return ['titleField' => 'product_title', 'extra' => ['product_slug', 'product_brand', 'product_meta']];
        }

        return ['titleField' => 'product_title', 'extra' => []];
    }
}
