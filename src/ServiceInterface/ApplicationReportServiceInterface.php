<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationSummaryDTO;

interface ApplicationReportServiceInterface
{
    public function buildSummary(): ApplicationSummaryDTO;
}
