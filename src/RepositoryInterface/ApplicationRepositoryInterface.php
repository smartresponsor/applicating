<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationEntity;

interface ApplicationRepositoryInterface
{
    public function findOneBySlug(string $slug): ?ApplicationEntity;

    /** @return list<ApplicationEntity> */
    public function findOrderedForAdmin(): array;

    public function countAllApplications(): int;

    public function countPublished(): int;

    public function save(ApplicationEntity $application): void;
}
