<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFlow;

interface PolicyConnectorInterface
{
    public function execute(array $context): array;
}
