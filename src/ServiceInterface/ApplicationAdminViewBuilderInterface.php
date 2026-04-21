<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\ApplicationAdminIndexRow;
use App\Applicating\DTO\Application\ApplicationAdminShowView;
use App\Applicating\Entity\Application;

interface ApplicationAdminViewBuilderInterface
{
    /** @param list<Application> $applications
     * @return list<ApplicationAdminIndexRow>
     */
    public function buildIndexRows(array $applications): array;

    public function buildShowView(Application $application): ApplicationAdminShowView;
}
