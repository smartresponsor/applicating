<?php
declare(strict_types=1);
namespace App\Component\Product\PolicyIntelligence\Insights;

final class InsightEngine
{
    /**
     * Простые эвристики:
     * - Низкий predictive score -> STOP rollout
     * - Высокий queue_depth -> SCALE scheduler
     * - Рост fails -> INVESTIGATE federation connectivity
     */
    public function derive(array $agg): array
    {
        $insights = [];
        if (($agg['predictive_avg_score'] ?? 1) < 0.55) {
            $insights[] = ['severity'=>'high','action'=>'STOP_ROLLOUT','msg'=>'Низкий средний predictive score (<0.55)'];
        }
        if (($agg['replication_queue_depth'] ?? 0) > 50) {
            $insights[] = ['severity'=>'medium','action'=>'SCALE_SCHEDULER','msg'=>'Очередь репликаций > 50'];
        }
        if (($agg['replication_failures_5xx'] ?? 0) > 5) {
            $insights[] = ['severity'=>'high','action'=>'CHECK_FEDERATION','msg'=>'Ошибки репликации за окно > 5'];
        }
        if (($agg['ledger_blocks_appended'] ?? 0) == 0) {
            $insights[] = ['severity'=>'low','action'=>'VERIFY_LEDGER','msg':'Нет новых блоков в Ledger в окне'];
        }
        return $insights;
    }
}
