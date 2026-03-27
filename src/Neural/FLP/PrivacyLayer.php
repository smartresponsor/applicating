<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\FLP;

final class PrivacyLayer
{
    public function anonymize(string $tenantId): string
    {
        return hash('sha256', $tenantId.'#flp');
    }

    /** Добавление гауссовского шума (наивно) к диффам. */
    public function addNoise(array $diff, float $sigma): array
    {
        $w = (float) ($diff['w'] ?? 1.0);
        $noise = $this->gauss(0.0, $sigma);

        return ['w' => $w + $noise];
    }

    private function gauss(float $mu, float $sigma): float
    {
        $u = 1 - (mt_rand() / mt_getrandmax());
        $v = 1 - (mt_rand() / mt_getrandmax());
        $z = sqrt(-2.0 * log($u)) * cos(2.0 * M_PI * $v);

        return $mu + $sigma * $z;
    }
}
