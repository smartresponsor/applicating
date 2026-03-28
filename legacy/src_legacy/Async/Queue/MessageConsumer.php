<?php
declare(strict_types=1);

namespace App\Component\Product\Async\Queue;

use Throwable;

final class MessageConsumer
{
    public function __construct(
        private readonly callable $handler,
        private readonly int $maxAttempts = 5
    ) {}

    /**
     * @param iterable<array{topic:string, payload:array, ack: callable, nack: callable}> $messages
     */
    public function run(iterable $messages): void
    {
        foreach ($messages as $m) {
            $attempt = 0;
            while (true) {
                try {
                    ($this->handler)($m['topic'], $m['payload']);
                    ($m['ack'])();
                    break;
                } catch (Throwable $e) {
                    $attempt++;
                    if ($attempt >= $this->maxAttempts) {
                        ($m['nack'])($e);
                        break;
                    }
                    usleep(100000 * $attempt);
                }
            }
        }
    }
}
