<?php

declare(strict_types=1);

namespace App\Applicating\BuilderInterface;

use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\Entity\ApplicationEntity;

interface ApplicationAdminViewBuilderInterface
{
    /** @param list<ApplicationEntity> $applications
     * @return list<ApplicationAdminIndexRowDTO>
     */
    public function buildIndexRows(array $applications): array;

    public function buildShowView(ApplicationEntity $application): ApplicationAdminShowViewDTO;
}
