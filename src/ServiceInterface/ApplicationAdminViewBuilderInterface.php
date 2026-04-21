<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationAdminIndexRow;
use App\Application\DTO\Application\ApplicationAdminShowView;
use App\Application\Entity\Application;

interface ApplicationAdminViewBuilderInterface
{
    /** @param list<Application> $applications
     * @return list<ApplicationAdminIndexRow>
     */
    public function buildIndexRows(array $applications): array;

    public function buildShowView(Application $application): ApplicationAdminShowView;
}
