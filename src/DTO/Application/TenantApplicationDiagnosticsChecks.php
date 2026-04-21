<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

final readonly class TenantApplicationDiagnosticsChecks
{
    /** @param list<string> $accessPolicyKeys */
    public function __construct(
        public bool $manifestPresent,
        public bool $releasePresent,
        public string $sandboxProfile,
        public array $accessPolicyKeys,
    ) {
    }

    /** @return array{manifest_present:bool,release_present:bool,sandbox_profile:string,access_policy_keys:list<string>} */
    public function toArray(): array
    {
        return [
            'manifest_present' => $this->manifestPresent,
            'release_present' => $this->releasePresent,
            'sandbox_profile' => $this->sandboxProfile,
            'access_policy_keys' => $this->accessPolicyKeys,
        ];
    }
}
