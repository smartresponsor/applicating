<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\ApplicationPublishEligibility;
use App\Applicating\Entity\Application;

interface ApplicationPublishEligibilityServiceInterface
{
    /** @return list<ApplicationPublishEligibility> */
    public function buildEligibilityMap(Application $application): array;
}
