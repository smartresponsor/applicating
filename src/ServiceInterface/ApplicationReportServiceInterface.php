<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\ApplicationSummary;

interface ApplicationReportServiceInterface
{
    public function buildSummary(): ApplicationSummary;
}
