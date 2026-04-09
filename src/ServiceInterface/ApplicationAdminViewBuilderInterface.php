<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\ApplicationAdminIndexRow;
use App\DTO\Application\ApplicationAdminShowView;
use App\Entity\Application;

interface ApplicationAdminViewBuilderInterface
{
    /** @param list<Application> $applications
     *  @return list<ApplicationAdminIndexRow>
     */
    public function buildIndexRows(array $applications): array;

    public function buildShowView(Application $application): ApplicationAdminShowView;
}
