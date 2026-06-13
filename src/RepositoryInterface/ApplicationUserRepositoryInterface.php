<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationUser;

interface ApplicationUserRepositoryInterface
{
    public function findOneByIdentifier(string $userIdentifier): ?ApplicationUser;

    public function countActiveUsers(): int;
}
