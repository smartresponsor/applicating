<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CategoryMoveCommand;
use App\ServiceInterface\CategoryMoveInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryMoveCommandTest extends TestCase
{
    public function testExecutePrintsMovePayloadWithDryRunAndLocale(): void
    {
        $service = new class implements CategoryMoveInterface {
            /** @var array<string,mixed> */
            public array $seen = [];

            public function move(string $nodeId, string $newParentId, string $treeId, string $policy, bool $dryRun = false, ?string $locale = null): array
            {
                $this->seen = [
                    'nodeId' => $nodeId,
                    'newParentId' => $newParentId,
                    'treeId' => $treeId,
                    'policy' => $policy,
                    'dryRun' => $dryRun,
                    'locale' => $locale,
                ];

                return [2, [['from' => '/old', 'to' => '/new']]];
            }
        };

        $command = new CategoryMoveCommand($service);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'nodeId' => 'cat-100',
            'newParentId' => 'cat-200',
            'treeId' => 'main-tree',
            'policy' => 'strict',
            '--dry-run' => true,
            '--locale' => 'en_US',
        ]);

        self::assertSame(0, $exitCode);
        self::assertSame([
            'nodeId' => 'cat-100',
            'newParentId' => 'cat-200',
            'treeId' => 'main-tree',
            'policy' => 'strict',
            'dryRun' => true,
            'locale' => 'en_US',
        ], $service->seen);

        /** @var array{changed:int,redirects:list<array{from:string,to:string}>} $payload */
        $payload = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(2, $payload['changed']);
        self::assertCount(1, $payload['redirects']);
        self::assertSame('/old', $payload['redirects'][0]['from']);
        self::assertSame('/new', $payload['redirects'][0]['to']);
    }
}
