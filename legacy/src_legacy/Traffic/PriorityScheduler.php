<?php

declare(strict_types=1);

namespace App\Component\Product\Traffic;

final class PriorityScheduler
{
    public function __construct(private readonly PriorityQueueService $q, private readonly TrafficShaper $shaper)
    {
    }

    /** Выбирает задание с учётом шейпинга; возвращает null, если лимит исчерпан. */
    public function next(): ?array
    {
        $item = $this->q->dequeue();
        if (!$item) {
            return null;
        }
        if (!$this->shaper->allow((string) $item['tenant_id'])) {
            // не можем сейчас — возвращаем в очередь как queued (хвост)
            $this->q->complete((int) $item['id'], false);

            return null;
        }

        return $item;
    }
}
