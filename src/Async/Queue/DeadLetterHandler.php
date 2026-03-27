<?php

declare(strict_types=1);

namespace App\Component\Product\Async\Queue;

final class DeadLetterHandler
{
    public function __construct(private readonly \PDO $db, private readonly PublisherInterface $publisher)
    {
    }

    /** @param callable(array):array $mutator */
    public function retry(int $limit = 50, ?callable $mutator = null): int
    {
        $rows = $this->db->query('SELECT * FROM dead_letter ORDER BY failed_at LIMIT '.(int) $limit)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $cnt = 0;
        foreach ($rows as $row) {
            $payload = json_decode($row['payload'], true);
            if ($mutator) {
                $payload = $mutator($payload);
            }
            $topic = sprintf('%s.%s', $row['aggregate_type'], $row['event_type']);
            $this->publisher->publish($topic, $payload);
            $this->db->prepare('DELETE FROM dead_letter WHERE id=:id')->execute(['id' => (int) $row['id']]);
            ++$cnt;
        }

        return $cnt;
    }
}
