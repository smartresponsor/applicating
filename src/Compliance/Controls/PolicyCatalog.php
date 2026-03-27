<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\Controls;

final class PolicyCatalog
{
    /** @return array<string,array> */
    public function list(): array
    {
        return [
            'gdpr' => ['articles' => ['6', '7', '13', '17', '30'], 'name' => 'GDPR'],
            'hipaa' => ['rules' => ['Privacy', 'Security'], 'name' => 'HIPAA'],
            'soc2' => ['trust_services' => ['Security', 'Availability', 'Confidentiality'], 'name' => 'SOC2'],
            'esg' => ['kpi' => ['E', 'S', 'G'], 'name' => 'ESG'],
        ];
    }
}
