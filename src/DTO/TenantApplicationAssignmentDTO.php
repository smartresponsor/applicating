<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class TenantApplicationAssignmentDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $tenantKey = '';

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+\.\d+\.\d+(?:-[a-z0-9.-]+)?$/i')]
    public string $installedVersion = '';

    public bool $enabled = true;

    public bool $billingActive = true;

    public string $accessPolicy = "{\n  \"scope\": \"tenant\",\n  \"roles\": [\"ROLE_APPLICATION_USER\"]\n}";
}
