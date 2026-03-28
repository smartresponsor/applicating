<?php

declare(strict_types=1);

namespace App\Component\Product\AutoRemediation;

use Doctrine\DBAL\Connection;

final class RemediationEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function plan(array $aggregate, array $insights): array
    {
        $actions = [];
        foreach ($insights as $i) {
            $act = (string) ($i['action'] ?? '');
            if ('' === $act) {
                continue;
            }
            $actions.append([
                'action' => $act,
                'severity' => (string) ($i['severity'] ?? 'low'),
                'reason' => (string) ($i['msg'] ?? 'insight'),
                'created_at' => gmdate('c'),
            ]);
        }

        return $actions;
    }

    public function log(string $action, string $status, string $details = ''): void
    {
        $this->db->insert('policy_remediation_log', [
            'ts' => gmdate('c'),
            'action' => $action,
            'status' => $status,
            'details' => $details,
        ]);
    }
}
