<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationReadinessDTO;

interface ApplicationReadinessServiceInterface
{
    public function buildReadiness(string $applicationSlug): ApplicationReadinessDTO;
}
