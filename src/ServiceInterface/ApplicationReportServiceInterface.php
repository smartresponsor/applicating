<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationSummary;

interface ApplicationReportServiceInterface
{
    public function buildSummary(): ApplicationSummary;
}
