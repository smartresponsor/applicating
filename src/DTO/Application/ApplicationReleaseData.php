<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

use Symfony\Component\Validator\Constraints as Assert;

final class ApplicationReleaseData
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+\.\d+\.\d+(?:-[a-z0-9.-]+)?$/i')]
    public string $version = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    public string $channel = 'stable';

    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    public string $checksum = '';

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $downloadUrl = '';

    #[Assert\NotBlank]
    public string $releaseNotes = '';
}
