<?php

declare(strict_types=1);

namespace App\Component\Product\Async\Queue;

final class OutboxRelay
{
    public function __construct(
        private readonly \PDO $db,
        private readonly PublisherInterface $publisher,
        private readonly int $batchSize = 100,
        private readonly int $baseBackoffSec = 5,
    ) {
    }

    public function tick(): int
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $sql = 'SELECT * FROM outbox WHERE next_attempt_at IS NULL OR next_attempt_at <= :now ORDER BY created_at LIMIT :lim';
        $st = $this->db->prepare($sql);
        $st->bindValue('now', $now);
        $st->bindValue('lim', $this->batchSize, \PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $processed = 0;

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $payload = json_decode($row['payload'], true);
            $topic = sprintf('%s.%s', $row['aggregate_type'], $row['event_type']);
            try {
                $this->publisher->publish($topic, $payload);
                $this->db->prepare('DELETE FROM outbox WHERE id = :id')->execute(['id' => $id]);
                ++$processed;
            } catch (\Throwable $e) {
                $attempts = (int) $row['attempts'] + 1;
                $next = (new \DateTimeImmutable('+'.($attempts * $this->baseBackoffSec).' seconds'))->format('Y-m-d H:i:s');
                $stmt = $this->db->prepare('UPDATE outbox SET attempts=:att, next_attempt_at=:next WHERE id=:id');
                $stmt->execute(['att' => $attempts, 'next' => $next, 'id' => $id]);
                if ($attempts >= 5) {
                    $ins = $this->db->prepare('INSERT INTO dead_letter(aggregate_type, aggregate_id, event_type, payload, headers, error, attempts) VALUES (:ag,:aid,:ev,:pl,:hd,:er,:at)');
                    $ins->execute([
                        'ag' => $row['aggregate_type'],
                        'aid' => $row['aggregate_id'],
                        'ev' => $row['event_type'],
                        'pl' => $row['payload'],
                        'hd' => $row['headers'],
                        'er' => $e->getMessage(),
                        'at' => $attempts,
                    ]);
                    $this->db->prepare('DELETE FROM outbox WHERE id = :id')->execute(['id' => $id]);
                }
            }
        }

        return $processed;
    }
}
