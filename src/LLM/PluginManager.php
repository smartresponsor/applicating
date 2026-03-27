<?php

declare(strict_types=1);

namespace App\Component\Product\LLM;

final class PluginManager
{
    /** @var array<int,PluginInterface> */
    private array $plugins = [];

    public function register(PluginInterface $p): void
    {
        $this->plugins[] = $p;
    }

    /** @return array<int,PluginInterface> */
    public function all(): array
    {
        return $this->plugins;
    }
}
