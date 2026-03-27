<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Event;

final class ApiRequestEvent
{
    /** @param array<string,mixed> $ctx */
    public function __construct(public readonly string $path, public readonly array $ctx = []) {}
}
