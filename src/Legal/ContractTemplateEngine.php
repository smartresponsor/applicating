<?php

declare(strict_types=1);

namespace App\Component\Product\Legal;

use Doctrine\DBAL\Connection;

/**
 * Простой шаблонизатор контрактов на плейсхолдерах {{var}}.
 * Данные подтягиваются из Compliance Nexus и System Audit.
 */
final class ContractTemplateEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return string Рендерит шаблон с плейсхолдерами. */
    public function render(string $template, array $ctx): string
    {
        $out = $template;
        foreach ($ctx as $k => $v) {
            $out = str_replace('{{'.$k.'}}', (string) $v, $out);
        }

        return $out;
    }

    /** Формирует контекст для DPA/SLA на основе БД. */
    public function defaultContext(): array
    {
        $retDays = (int) ($this->db->fetchOne('SELECT ttl_days FROM compliance_retention ORDER BY ts DESC LIMIT 1') ?? 30);
        $dpia = (float) ($this->db->fetchOne('SELECT risk_score FROM compliance_dpia ORDER BY ts DESC LIMIT 1') ?? 0.3);
        $consented = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM compliance_consent WHERE granted=1 AND ts>NOW()-INTERVAL '30 days'") ?? 0);

        return [
            'company_name' => 'YourCompany',
            'retention_days' => $retDays,
            'dpia_risk' => $dpia,
            'consents_30d' => $consented,
            'today' => gmdate('Y-m-d'),
        ];
    }
}
