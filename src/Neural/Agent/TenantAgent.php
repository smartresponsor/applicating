<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\Agent;

use Doctrine\DBAL\Connection;

/**
 * Мини-агент для конкретного арендатора: хранит состояние и принимает локальные решения.
 */
final class TenantAgent
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает вектор признаков (упрощённо) по usage/telemetry. */
    public function features(string $tenantId): array
    {
        $qps = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(qps),0) FROM telemetry_qps WHERE tenant_id=? AND ts>= (NOW()-INTERVAL '1 hour')", [$tenantId]) ?? 0.0);
        $err = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(error_rate),0) FROM telemetry_errors WHERE tenant_id=? AND ts>= (NOW()-INTERVAL '1 hour')", [$tenantId]) ?? 0.0);
        $spent = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount_usd),0) FROM tenant_usage WHERE tenant_id=? AND to_char(ts,'YYYY-MM')=to_char(NOW(),'YYYY-MM')", [$tenantId]) ?? 0.0);

        return ['qps' => $qps, 'error' => $err, 'spent' => $spent];
    }

    /** Простейшее решение: adjust_factor на основе признаков. */
    public function decide(string $tenantId): array
    {
        $f = $this->features($tenantId);
        $adjust = 1.0;
        if ($f['error'] > 0.02) {
            $adjust -= 0.1;
        }
        if ($f['qps'] > 200) {
            $adjust += 0.1;
        }
        if ($f['spent'] > 500) {
            $adjust += 0.05;
        }
        $this->db->insert('neural_agent_decisions', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'decision' => json_encode(['adjust' => $adjust]),
        ]);

        return ['tenant' => $tenantId, 'adjust' => $adjust, 'features' => $f];
    }
}
