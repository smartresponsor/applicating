<?php

declare(strict_types=1);

namespace App\ServiceInterface;

interface ApplicationReportServiceInterface
{
    /** @return array<string, mixed> */
    public function buildSummary(): array;
}
