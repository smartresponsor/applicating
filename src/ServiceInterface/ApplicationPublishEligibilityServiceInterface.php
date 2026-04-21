<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationPublishEligibility;
use App\Application\Entity\Application;

interface ApplicationPublishEligibilityServiceInterface
{
    /** @return list<ApplicationPublishEligibility> */
    public function buildEligibilityMap(Application $application): array;
}
