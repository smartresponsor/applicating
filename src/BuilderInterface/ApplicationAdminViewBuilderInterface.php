<?php

declare(strict_types=1);

namespace App\Applicating\BuilderInterface;

use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\Entity\Application\ApplicationEntity;

/**
 * Builds presentation DTOs for the Applicating administrative application views.
 */
interface ApplicationAdminViewBuilderInterface
{
    /**
     * Build the ordered row projection used by the application index view.
     *
     * @param list<ApplicationEntity> $applications
     *
     * @return list<ApplicationAdminIndexRowDTO>
     */
    public function buildIndexRows(array $applications): array;

    /**
     * Build the complete application detail projection, including lifecycle collections.
     */
    public function buildShowView(ApplicationEntity $application): ApplicationAdminShowViewDTO;
}
