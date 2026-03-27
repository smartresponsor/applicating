<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing\DTO;

final class IncidentReport
{
    public function __construct(
        public string $tenantId,
        public string $component,
        public string $status,     // firing|resolved
        public string $error,      // текст ошибки/алерта
        public string $ts,
    ) {
    }
}
