<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

final class PiiMasker
{
    /** @param array<string,mixed> $data */
    public function mask(array $data): array
    {
        $keys = ['password', 'token', 'authorization', 'email', 'phone', 'card', 'ssn'];
        $out = $data;
        foreach ($out as $k => $v) {
            if (in_array(strtolower((string) $k), $keys, true)) {
                $out[$k] = '***';
            }
        }

        return $out;
    }
}
