<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationPublishEligibilityDTO;
use App\Applicating\Entity\ApplicationEntity;

interface ApplicationPublishEligibilityServiceInterface
{
    /** @return list<ApplicationPublishEligibilityDTO> */
    public function buildEligibilityMap(ApplicationEntity $application): array;
}
