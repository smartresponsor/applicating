<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Action;

use Doctrine\DBAL\Connection;

final class BusinessActionOrchestrator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Имитируем применение: обновление плана/цены/кредита в БД.
     *
     * @param array<string,mixed> $params
     *
     * @return array{ok:bool,updated:int}
     */
    public function apply(string $tenantId, string $decision, array $params): array
    {
        $updated = 0;
        $ok = true;
        try {
            switch ($decision) {
                case 'price_adjust_up':
                    $pct = (float) ($params['percent'] ?? 0.03);
                    // пример: увеличить базовую цену в таблице pricing на pct
                    $updated = (int) $this->db->executeStatement(
                        'UPDATE pricing SET base_price_cents = CAST(base_price_cents * (1 + :pct) AS INT) WHERE tenant_id = :t',
                        ['pct' => $pct, 't' => $tenantId]
                    );
                    break;
                case 'sla_raise':
                    $uptime = (string) ($params['target_uptime'] ?? '99.9');
                    $updated = (int) $this->db->executeStatement(
                        'UPDATE sla_plans SET target_uptime = :u WHERE tenant_id = :t',
                        ['u' => $uptime, 't' => $tenantId]
                    );
                    break;
                case 'promo_credit':
                    $credit = (int) ($params['credit_cents'] ?? 2000);
                    $updated = (int) $this->db->executeStatement(
                        'INSERT INTO credits (tenant_id, credit_cents, reason) VALUES (:t, :c, :r)',
                        ['t' => $tenantId, 'c' => $credit, 'r' => 'ABI promo']
                    );
                    break;
                default:
                    $ok = true;
                    $updated = 0;
            }
        } catch (\Throwable $e) {
            $ok = false;
        }

        return ['ok' => $ok, 'updated' => $updated];
    }
}
