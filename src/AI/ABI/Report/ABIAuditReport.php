<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Report;

final class ABIAuditReport
{
    /** @param array<string,mixed> $event */
    public function toJson(array $event): string
    {
        return json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
