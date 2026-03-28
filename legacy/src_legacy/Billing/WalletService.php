<?php

declare(strict_types=1);

namespace App\Component\Product\Billing;

use Doctrine\DBAL\Connection;

final class WalletService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function getBalance(string $tenantId): float
    {
        return (float) ($this->db->fetchOne('SELECT balance_usd FROM billing_wallets WHERE tenant_id=?', [$tenantId]) ?? 0.0);
    }

    public function topup(string $tenantId, float $amountUsd, string $source): void
    {
        $this->db->executeStatement('INSERT INTO billing_wallets(tenant_id, balance_usd) VALUES(?, ?) ON CONFLICT(tenant_id) DO UPDATE SET balance_usd = billing_wallets.balance_usd + EXCLUDED.balance_usd', [$tenantId, $amountUsd]);
        $this->db->insert('billing_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => 'topup', 'meta' => json_encode(['source' => $source, 'amount' => $amountUsd]),
        ]);
    }

    public function charge(string $tenantId, float $amountUsd, string $reason): bool
    {
        $bal = $this->getBalance($tenantId);
        if ($bal < $amountUsd) {
            return false;
        }
        $this->db->update('billing_wallets', ['balance_usd' => $bal - $amountUsd], ['tenant_id' => $tenantId]);
        $this->db->insert('billing_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => 'charge', 'meta' => json_encode(['reason' => $reason, 'amount' => $amountUsd]),
        ]);

        return true;
    }
}
