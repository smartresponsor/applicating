<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category\Infrastructure;

use App\Infrastructure\CategoryAuditLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

final class CategoryAuditLoggerTest extends TestCase
{
    public function testLog(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var list<array{0:string,1:string,2:array<string,mixed>}> */
            public array $records = [];

            /** @param array<string,mixed> $context */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $normalizedLevel = is_scalar($level) || null === $level ? (string) $level : get_debug_type($level);
                $this->records[] = [$normalizedLevel, (string) $message, $context];
            }
        };
        $audit = new CategoryAuditLogger($logger);
        $audit->log('category.move', ['id' => 1]);
        $this->assertCount(1, $logger->records);
    }
}
