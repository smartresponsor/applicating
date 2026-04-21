<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\ApplicationReadiness;

interface ApplicationReadinessServiceInterface
{
    public function buildReadiness(string $applicationSlug): ApplicationReadiness;
}
