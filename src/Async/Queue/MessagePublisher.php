<?php

declare(strict_types=1);

namespace App\Component\Product\Async\Queue;

interface PublisherInterface
{
    /** @param array<string, mixed> $message */
    public function publish(string $topic, array $message): void;
}

final class RabbitPublisher implements PublisherInterface
{
    public function publish(string $topic, array $message): void
    {
        $conn = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'app', 'app', '/');
        $ch = $conn->channel();
        $ch->exchange_declare($topic, 'fanout', false, true, false);
        $body = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $msg = new \PhpAmqpLib\Message\AMQPMessage($body, ['content_type' => 'application/json']);
        $ch->basic_publish($msg, $topic);
        $ch->close();
        $conn->close();
    }
}

final class RedisStreamPublisher implements PublisherInterface
{
    public function __construct(private readonly \Redis $redis)
    {
    }

    public function publish(string $topic, array $message): void
    {
        $this->redis->xAdd($topic, '*', ['data' => json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }
}
