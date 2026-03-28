<?php

declare(strict_types=1);

namespace App\Component\Product\Stream;

final class FeedbackHandler
{
    public function __construct(private readonly \Doctrine\DBAL\Connection $db)
    {
    }

    /** Записывает факт и обновляет ошибку прогноза. */
    public function apply(string $tenantId, string $target, float $actual): bool
    {
        $row = $this->db->fetchAssociative(
            'SELECT * FROM forecast_results WHERE tenant_id=? AND target=? ORDER BY ts DESC LIMIT 1',
            [$tenantId, $target]
        );
        if (!$row) {
            return false;
        }
        $error = ($row['predicted'] ?? 0) - $actual;
        $this->db->update('forecast_results', [
            'actual' => $actual,
            'error' => $error,
            'feedback_score' => 1.0 / (1.0 + abs($error)),
        ], ['id' => $row['id']]);

        return true;
    }
}
