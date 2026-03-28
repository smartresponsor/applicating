<?php

declare(strict_types=1);

namespace App\Component\Product\Traffic;

use App\Component\Product\Traffic\Algo\TokenBucket;
use Doctrine\DBAL\Connection;

final class TrafficShaper
{
    /** @var array<string,TokenBucket> */
    private array $buckets = [];

    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает (rate, burst) исходя из плана и TrustScore. */
    private function limitsFor(string $tenantId): array
    {
        $plan = (string) ($this->db->fetchOne('SELECT plan FROM smartcloud_tenants WHERE tenant_id=?', [$tenantId]) ?? 'standard');
        $trust = (float) ($this->db->fetchOne('SELECT score FROM trustmesh_scores WHERE tenant_id=? ORDER BY ts DESC LIMIT 1', [$tenantId]) ?? 0.5);

        $base = match ($plan) {
            'vip' => [100.0, 200.0],
            'pro' => [50.0, 120.0],
            default => [20.0, 60.0],
        };
        // TrustScore добавляет до +25% к rate и +25% к burst
        $k = 1.0 + min(0.25, max(0.0, $trust * 0.25));

        return [$base[0] * $k, $base[1] * $k];
    }

    private function bucket(string $tenantId): TokenBucket
    {
        if (!isset($this->buckets[$tenantId])) {
            [$r,$b] = $this->limitsFor($tenantId);
            $this->buckets[$tenantId] = new TokenBucket($r, $b);
        }

        return $this->buckets[$tenantId];
    }

    public function allow(string $tenantId, int $cost = 1): bool
    {
        return $this->bucket($tenantId)->allow($cost);
    }
}
