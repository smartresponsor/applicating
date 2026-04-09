<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\ApplicationManifest;

final class ApplicationAdminManifestView
{
    /**
     * @param list<string> $capabilities
     * @param list<string> $runtimeHooks
     */
    public function __construct(
        public readonly string $identifier,
        public readonly string $manifestVersion,
        public readonly string $sandboxProfile,
        public readonly string $governanceState,
        public readonly array $capabilities,
        public readonly array $runtimeHooks,
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
