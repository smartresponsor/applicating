<?php

declare(strict_types=1);

namespace App\Component\Product\AbuseGuard;

use Doctrine\DBAL\Connection;

final class AbuseAnalyzer
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает true, если IP или tenant в бане. */
    public function isBanned(string $ip, string $tenant): bool
    {
        $ipBan = (bool) $this->db->fetchOne("SELECT 1 FROM abuse_bans WHERE kind='ip' AND value=? AND (expires_at IS NULL OR expires_at>NOW())", [$ip]);
        $tBan = (bool) $this->db->fetchOne("SELECT 1 FROM abuse_bans WHERE kind='tenant' AND value=? AND (expires_at IS NULL OR expires_at>NOW())", [$tenant]);

        return $ipBan || $tBan;
    }

    /** Наивная эвристика: если за последнюю минуту >N событий — подозрение. */
    public function isBursting(string $ip, string $tenant, int $threshold): bool
    {
        $cnt = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM abuse_events WHERE ts >= (NOW()-INTERVAL '60 seconds') AND (ip=? OR tenant_id=?)", [$ip, $tenant]) ?? 0);

        return $cnt >= $threshold;
    }

    public function log(string $ip, string $tenant, string $action, int $status): void
    {
        $this->db->insert('abuse_events', [
            'ts' => gmdate('c'), 'ip' => $ip, 'tenant_id' => $tenant, 'action' => $action, 'status' => $status,
        ]);
    }

    public function penalizeIP(string $ip, int $seconds): void
    {
        $this->db->insert('abuse_bans', [
            'kind' => 'ip', 'value' => $ip, 'reason' => 'burst-penalty', 'created_at' => gmdate('c'),
            'expires_at' => gmdate('c', time() + $seconds),
        ]);
    }
}
