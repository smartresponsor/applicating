<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationManifestDTO;

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
    public function normalizeManifestPayload(ApplicationManifestDTO $data): array;
}
