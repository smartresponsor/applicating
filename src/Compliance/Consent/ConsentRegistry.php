<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\Consent;

use Doctrine\DBAL\Connection;

final class ConsentRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function set(string $subjectId, string $purpose, bool $granted): void
    {
        $this->db->insert('compliance_consent', [
            'ts' => gmdate('c'),
            'subject_id' => $subjectId,
            'purpose' => $purpose,
            'granted' => $granted ? 1 : 0,
        ]);
    }

    public function get(string $subjectId): array
    {
        return $this->db->fetchAllAssociative('SELECT purpose, granted, ts FROM compliance_consent WHERE subject_id=? ORDER BY ts DESC', [$subjectId]) ?: [];
    }
}
