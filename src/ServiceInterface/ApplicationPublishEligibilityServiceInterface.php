<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\Entity\Application;

interface ApplicationPublishEligibilityServiceInterface
{
    /**
     * @return array<int, array{eligible: bool, reason: ?string}>
     */
    public function buildEligibilityMap(Application $application): array;
}
