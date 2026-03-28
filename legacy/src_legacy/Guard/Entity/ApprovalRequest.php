<?php

declare(strict_types=1);

namespace App\Component\Product\Guard\Entity;

final class ApprovalRequest
{
    public function __construct(
        public int $id,
        public string $tenant_id,
        public string $action,
        /** @var array<string,mixed> */
        public array $params,
        public string $risk_level, // low|medium|high
        public string $status,     // pending|approved|rejected
        public string $requested_by,
        public ?string $approved_by,
        public string $ts_request,
        public ?string $ts_approve,
        /** @var array<string,mixed> */
        public array $details = [],
    ) {
    }
}
