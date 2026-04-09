<?php

declare(strict_types=1);

namespace App\DTO\Application;

use Symfony\Component\Validator\Constraints as Assert;

final class ApplicationManifestData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 24)]
    public string $manifestVersion = '1.0.0';

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:\.[a-z0-9-]+)+$/')]
    public string $identifier = '';

    public string $capabilities = '';

    public string $permissions = '';

    public string $runtimeHooks = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 80)]
    public string $sandboxProfile = 'default';

    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    public string $governanceState = 'approved';
}
