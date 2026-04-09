<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\ApplicationPublishEligibility;
use App\Entity\Application;

interface ApplicationPublishEligibilityServiceInterface
{
    /** @return list<ApplicationPublishEligibility> */
    public function buildEligibilityMap(Application $application): array;
}
