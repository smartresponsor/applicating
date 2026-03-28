<?php

declare(strict_types=1);

namespace App\Component\Product\Legal\Validator;

use Doctrine\DBAL\Connection;

final class AssuranceValidator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Проверяет, что системные параметры соответствуют условиям контракта.
     *
     * @param array{minRetention:int,maxDpia:float} $rules
     */
    public function check(array $rules): array
    {
        $retDays = (int) ($this->db->fetchOne('SELECT ttl_days FROM compliance_retention ORDER BY ts DESC LIMIT 1') ?? 30);
        $dpia = (float) ($this->db->fetchOne('SELECT risk_score FROM compliance_dpia ORDER BY ts DESC LIMIT 1') ?? 0.3);

        $okRetention = $retDays >= (int) ($rules['minRetention'] ?? 30);
        $okDpia = $dpia <= (float) ($rules['maxDpia'] ?? 0.7);
        $ok = $okRetention && $okDpia;

        return [
            'ok' => $ok,
            'checks' => [
                'retention_days' => ['value' => $retDays, 'rule_min' => (int) ($rules['minRetention'] ?? 30), 'ok' => $okRetention],
                'dpia_risk' => ['value' => $dpia, 'rule_max' => (float) ($rules['maxDpia'] ?? 0.7), 'ok' => $okDpia],
            ],
        ];
    }
}
