<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application;

interface ApplicationRepositoryInterface
{
    /** @return list<Application> */
    public function findOrderedForAdmin(): array;

    public function countAllApplications(): int;

    public function countPublished(): int;
}
