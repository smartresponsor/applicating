<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategoryWorkflowTransitionCommand;
use App\Policy\CategoryWorkflowPolicy;
use App\Repository\CategoryWorkflowRepository;
use App\Service\CatalogWorkflowTransitionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryWorkflowTransitionCommandTest extends TestCase
{
    public function testExecutePrintsTransitionPayload(): void
    {
        $repository = new CategoryWorkflowRepository();
        $service = new CatalogWorkflowTransitionService($repository, new CategoryWorkflowPolicy());
        $command = new CategoryWorkflowTransitionCommand($service);

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'categoryId' => 'cat-100',
            'targetState' => 'approved',
            'actorId' => 'ops.user',
            'reason' => 'cli promotion',
        ]);

        self::assertSame(0, $exitCode);

        /** @var array{categoryId:string,fromState:string,toState:string,actorId:string,reason:string} $payload */
        $payload = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('cat-100', $payload['categoryId']);
        self::assertSame('draft', $payload['fromState']);
        self::assertSame('approved', $payload['toState']);
        self::assertSame('ops.user', $payload['actorId']);
        self::assertSame('cli promotion', $payload['reason']);
    }
}
