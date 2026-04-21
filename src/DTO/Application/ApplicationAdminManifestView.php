<?php

declare(strict_types=1);

namespace App\Applicating\DTO\Application;

use App\Applicating\Entity\ApplicationManifest;

final readonly class ApplicationAdminManifestView
{
    /**
     * @param list<string> $capabilities
     * @param list<string> $runtimeHooks
     */
    public function __construct(
        public string $identifier,
        public string $manifestVersion,
        public string $sandboxProfile,
        public string $governanceState,
        public array $capabilities,
        public array $runtimeHooks,
    ) {
    }

    public static function fromManifest(ApplicationManifest $manifest): self
    {
        return new self(
            identifier: $manifest->getIdentifier(),
            manifestVersion: $manifest->getManifestVersion(),
            sandboxProfile: $manifest->getSandboxProfile(),
            governanceState: $manifest->getGovernanceState(),
            capabilities: $manifest->getCapabilities(),
            runtimeHooks: $manifest->getRuntimeHooks(),
        );
    }
}
