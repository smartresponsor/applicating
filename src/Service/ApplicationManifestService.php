<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Application\ApplicationManifestData;
use App\Application\ServiceInterface\ApplicationManifestServiceInterface;

final class ApplicationManifestService implements ApplicationManifestServiceInterface
{
    public function splitLines(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => '' !== $line));
    }

    /**
     * @param list<string> $capabilities
     *
     * @return list<string>
     */
    private function normalizeCapabilities(array $capabilities): array
    {
        $normalized = [];

        foreach ($capabilities as $capability) {
            $canonical = 'catalog' === $capability ? 'listing' : $capability;
            if (!in_array($canonical, $normalized, true)) {
                $normalized[] = $canonical;
            }
        }

        return $normalized;
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
            'capabilities' => $this->normalizeCapabilities($this->splitLines($data->capabilities)),
            'permissions' => $this->splitLines($data->permissions),
            'runtimeHooks' => $this->splitLines($data->runtimeHooks),
            'sandboxProfile' => $data->sandboxProfile,
            'governanceState' => $data->governanceState,
        ];
    }
}
