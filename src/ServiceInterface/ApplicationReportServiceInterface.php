<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\ApplicationSummary;

interface ApplicationReportServiceInterface
{
    public function buildSummary(): ApplicationSummary;
}
