<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Application\ServiceInterface;

interface ApplicationHealthServiceInterface
{
    /**
     * @return array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string}
     */
    public function buildHealth(): array;

    /**
     * @return array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string}
     */
    public function buildReadiness(): array;
}
