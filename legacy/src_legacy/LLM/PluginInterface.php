<?php

declare(strict_types=1);

namespace App\Component\Product\LLM;

interface PluginInterface
{
    /** @return array<int,array{action:string, params:array<string,mixed>, score:float, rationale?:string}> */
    public function analyze(array $context): array;

    /** Метаданные плагина */
    public function metadata(): array;
}
