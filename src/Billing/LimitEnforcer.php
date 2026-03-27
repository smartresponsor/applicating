<?php

declare(strict_types=1);

namespace App\Component\Product\Billing;

use Doctrine\DBAL\Connection;

final class LimitEnforcer
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает true, если можно продолжать (баланс и лимиты в норме). */
    public function allow(string $tenantId): bool
    {
        $bal = (float) ($this->db->fetchOne('SELECT balance_usd FROM billing_wallets WHERE tenant_id=?', [$tenantId]) ?? 0.0);
        $lim = (float) ($this->db->fetchOne('SELECT monthly_limit_usd FROM billing_limits WHERE tenant_id=?', [$tenantId]) ?? 0.0);
        $spent = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount_usd),0) FROM billing_usage WHERE tenant_id=? AND to_char(ts,'YYYY-MM')=to_char(NOW(),'YYYY-MM')", [$tenantId]) ?? 0.0);
        if ($bal <= 0) {
            return false;
        }
        if ($lim > 0 && $spent >= $lim) {
            return false;
        }

        return true;
    }
}
