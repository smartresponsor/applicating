<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

use App\Application\Enum\ApplicationAccessLevel;
use Symfony\Component\Validator\Constraints as Assert;

final class ApplicationUpsertData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
    public string $slug = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $packageName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $developerName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    public string $listingSummary = '';

    #[Assert\NotBlank]
    public string $accessLevel = ApplicationAccessLevel::Public->value;

    #[Assert\Length(max: 120)]
    public ?string $billingCode = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public string $sandboxProfile = 'default';

    public bool $enabledByDefault = false;
}
