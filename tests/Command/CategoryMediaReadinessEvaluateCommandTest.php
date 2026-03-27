<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategoryMediaReadinessEvaluateCommand;
use App\ServiceInterface\CatalogDestinationMediaReadinessServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryMediaReadinessEvaluateCommandTest extends TestCase
{
    public function testExecutePrintsJsonPayload(): void
    {
        $service = $this->createMock(CatalogDestinationMediaReadinessServiceInterface::class);
        $service->method('evaluate')->willReturn(new class implements \App\EventInterface\CategoryDestinationMediaReadinessEvaluatedInterface {
            /**
             * @param array{publishable: bool, checks: array{destinationMediaPublishable: bool}} $payload
             */
            public function __construct(private readonly array $payload = ['publishable' => true, 'checks' => ['destinationMediaPublishable' => true]])
            {
            }

            public function destinationId(): string
            {
                return 'dest-1';
            }

            public function categoryId(): string
            {
                return 'cat-1';
            }

            public function channel(): string
            {
                return 'web';
            }

            public function locale(): string
            {
                return 'en_US';
            }

            public function publishable(): bool
            {
                return true;
            }

            /** @return list<string> */
            public function requiredMissing(): array
            {
                return [];
            }

            /** @return list<string> */
            public function warnings(): array
            {
                return [];
            }

            /** @return array{destinationMediaPublishable: bool} */
            public function checks(): array
            {
                return ['destinationMediaPublishable' => true];
            }

            /** @return list<string> */
            public function matchedBindingIds(): array
            {
                return [];
            }

            public function actorId(): string
            {
                return 'ops';
            }

            public function reason(): string
            {
                return 'check';
            }

            public function occurredAt(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }

            public function payload(): array
            {
                return $this->payload;
            }
        });

        $command = new CategoryMediaReadinessEvaluateCommand($service);
        $tester = new CommandTester($command);
        $tester->execute([
            'categoryId' => 'cat-1',
            'actorId' => 'ops',
            'reason' => 'check',
            '--destination' => '{"channel":"web","locale":"en_US"}',
        ]);

        self::assertStringContainsString('"publishable":true', str_replace(['
', '
', ' '], '', $tester->getDisplay()));
    }
}
