<?php

declare(strict_types=1);

namespace App\Component\Product\DeveloperHub;

final class SubgraphTester
{
    public function validateSDL(string $sdl): array
    {
        $errors = [];
        if (!str_contains($sdl, 'type Query')) {
            $errors[] = 'Missing type Query';
        }
        if (strlen($sdl) < 40) {
            $errors[] = 'SDL too short';
        }

        return $errors;
    }
}
