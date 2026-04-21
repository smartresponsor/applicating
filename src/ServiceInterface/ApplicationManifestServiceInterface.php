<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationManifestData;

interface ApplicationManifestServiceInterface
{
    /** @return list<string> */
    public function splitLines(string $value): array;

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
    public function normalizeManifestPayload(ApplicationManifestData $data): array;
}
