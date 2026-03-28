<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Consent;

use Doctrine\DBAL\Connection;

final class ConsentRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function set(string $tenantId, string $subjectId, string $purpose, bool $granted): void
    {
        $this->db->insert('consents', [
            'ts' => gmdate('c'),
            'tenant_id' => $tenantId,
            'subject_id' => $subjectId,
            'purpose' => $purpose,
            'granted' => $granted,
        ]);
    }
}
