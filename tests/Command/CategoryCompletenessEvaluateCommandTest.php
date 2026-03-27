<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategoryCompletenessEvaluateCommand;
use App\Policy\CategoryCompletenessPolicy;
use App\Service\CatalogCompletenessService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryCompletenessEvaluateCommandTest extends TestCase
{
    public function testExecutePrintsCompletenessPayload(): void
    {
        $service = new CatalogCompletenessService(new CategoryCompletenessPolicy());
        $command = new CategoryCompletenessEvaluateCommand($service);

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'categoryId' => 'cat-200',
            'actorId' => 'ops.user',
            'reason' => 'cli completeness',
            '--payload' => json_encode([
                'slug' => 'outdoor-lights',
                'seo' => ['title' => 'Outdoor Lights', 'description' => 'Shop outdoor lights'],
                'content' => ['body' => 'Merch copy'],
                'locale' => ['enabled' => ['en_US']],
                'media' => ['primaryAssetId' => 'asset-1'],
                'aliases' => ['garden-lights'],
                'presentation' => ['bannerId' => 'banner-1', 'htmlBlockId' => 'html-1'],
            ], JSON_THROW_ON_ERROR),
        ]);

        self::assertSame(0, $exitCode);
        $payload = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);

        self::assertSame('cat-200', $payload['categoryId']);
        self::assertTrue($payload['complete']);
        self::assertSame(100, $payload['score']);
    }
}
