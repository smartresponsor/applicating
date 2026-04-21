<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\ServiceInterface\ApplicationHealthServiceInterface;
use Doctrine\DBAL\Connection;

final readonly class ApplicationHealthService implements ApplicationHealthServiceInterface
{
    public function __construct(private Connection $connection)
    {
    }

    private static function now(): string
    {
        return new \DateTimeImmutable()->format(DATE_ATOM);
    }

    /**
     * @return array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string}
     */
    public function buildHealth(): array
    {
        $databaseStatus = 'up';
        $databaseMessage = 'Connection check skipped for liveness.';

        return [
            'status' => 'ok',
            'checks' => [
                'database' => [
                    'status' => $databaseStatus,
                    'message' => $databaseMessage,
                ],
            ],
            'generatedAt' => self::now(),
        ];
    }

    /**
     * @return array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string}
     */
    public function buildReadiness(): array
    {
        try {
            $this->connection->fetchOne('SELECT 1');

            return [
                'status' => 'ready',
                'checks' => [
                    'database' => [
                        'status' => 'up',
                        'message' => 'Database connectivity verified.',
                    ],
                ],
                'generatedAt' => self::now(),
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'not_ready',
                'checks' => [
                    'database' => [
                        'status' => 'down',
                        'message' => $exception->getMessage(),
                    ],
                ],
                'generatedAt' => self::now(),
            ];
        }
    }
}
