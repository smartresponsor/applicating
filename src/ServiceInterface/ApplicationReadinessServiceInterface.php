<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationReadiness;

interface ApplicationReadinessServiceInterface
{
    public function buildReadiness(string $applicationSlug): ApplicationReadiness;
}
