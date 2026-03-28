<?php

declare(strict_types=1);

namespace App\Component\Product\AI;

use Doctrine\DBAL\Connection;

final class GovernanceAI
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Рекомендует месячный лимит на основе последних расходов и риска превышения. */
    public function recommendLimit(string $tenantId): float
    {
        $spent = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount_usd),0) FROM tenant_usage WHERE tenant_id=? AND to_char(ts,'YYYY-MM')=to_char(NOW(),'YYYY-MM')", [$tenantId]) ?? 0.0);
        $risk = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(error_rate),0) FROM telemetry_errors WHERE tenant_id=? AND ts >= (NOW() - INTERVAL '7 day')", [$tenantId]) ?? 0.0);
        $k = 1.2 + min(0.5, $risk); // чем выше риск, тем больше буфер

        return round($spent * $k + 20, 2);
    }
}
