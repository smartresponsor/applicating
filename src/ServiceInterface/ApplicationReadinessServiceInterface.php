<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\ApplicationReadiness;

interface ApplicationReadinessServiceInterface
{
    public function buildReadiness(string $applicationSlug): ApplicationReadiness;
}
