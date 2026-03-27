<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Event;

final class ApiErrorEvent
{
    public function __construct(public readonly string $path, public readonly string $type, public readonly string $message, public readonly int $status) {}
}
