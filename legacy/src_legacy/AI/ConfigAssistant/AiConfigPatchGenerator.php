<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ConfigAssistant;

final class AiConfigPatchGenerator
{
    public function generatePatch(array $issues): string
    {
        $lines = ['# AI-generated configuration patch suggestions'];
        foreach ($issues as $i) {
            $lines[] = '- fix: '.$i;
        }

        return implode("\n", $lines);
    }
}
