<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\ApplicationManifestEntity;

/**
 * Represents manifest governance and runtime metadata on the application detail surface.
 */
final readonly class ApplicationAdminManifestViewDTO
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

    /**
     * Projects one persisted manifest into the administration detail view contract.
     */
    public static function fromManifest(ApplicationManifestEntity $manifest): self
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
