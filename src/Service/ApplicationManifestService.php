<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationManifestData;
use App\ServiceInterface\ApplicationManifestServiceInterface;

final class ApplicationManifestService implements ApplicationManifestServiceInterface
{
    public function splitLines(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => '' !== $line));
    }

    /**
     * @return array{
     *     manifestVersion: string,
     *     identifier: string,
     *     capabilities: list<string>,
     *     permissions: list<string>,
     *     runtimeHooks: list<string>,
     *     sandboxProfile: string,
     *     governanceState: string
     * }
     */
    public function normalizeManifestPayload(ApplicationManifestData $data): array
    {
        return [
            'manifestVersion' => $data->manifestVersion,
            'identifier' => $data->identifier,
            'capabilities' => $this->splitLines($data->capabilities),
            'permissions' => $this->splitLines($data->permissions),
            'runtimeHooks' => $this->splitLines($data->runtimeHooks),
            'sandboxProfile' => $data->sandboxProfile,
            'governanceState' => $data->governanceState,
        ];
    }
}
