<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Consensus;

/** Last-Write-Wins Map (in-memory holder; persist via export/import). */
final class LwwMap
{
    /** @var array<string,array{ts:int,val:mixed}> */
    private array $data = [];

    public function put(string $key, mixed $val, int $ts): void
    {
        $cur = $this->data[$key]['ts'] ?? 0;
        if ($ts >= $cur) {
            $this->data[$key] = ['ts' => $ts, 'val' => $val];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key]['val'] ?? $default;
    }

    /** @param array<string,array{ts:int,val:mixed}> $entries */
    public function merge(array $entries): void
    {
        foreach ($entries as $k => $v) {
            $ts = (int) ($v['ts'] ?? 0);
            $val = $v['val'] ?? null;
            $this->put((string) $k, $val, $ts);
        }
    }

    /** @return array<string,array{ts:int,val:mixed}> */
    public function export(): array
    {
        return $this->data;
    }
}
