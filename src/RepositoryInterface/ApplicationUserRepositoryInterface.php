<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationUserEntity;

interface ApplicationUserRepositoryInterface
{
    public function findOneByIdentifier(string $userIdentifier): ?ApplicationUserEntity;

    public function countActiveUsers(): int;

    public function save(ApplicationUserEntity $user): void;
}
