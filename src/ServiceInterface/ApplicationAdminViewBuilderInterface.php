<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\Entity\Application;

interface ApplicationAdminViewBuilderInterface
{
    /** @param list<Application> $applications
     * @return list<ApplicationAdminIndexRowDTO>
     */
    public function buildIndexRows(array $applications): array;

    public function buildShowView(Application $application): ApplicationAdminShowViewDTO;
}
