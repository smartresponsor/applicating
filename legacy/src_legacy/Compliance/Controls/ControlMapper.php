<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\Controls;

use Doctrine\DBAL\Connection;

final class ControlMapper
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сопоставляет внутренние политики Smartresponsor с внешними нормами. */
    public function map(string $standard): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT key, value FROM compliance_mapping WHERE standard=?', [$standard]);
        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r['key']] = $r['value'];
        }

        return $map;
    }
}
