<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\ServiceInterface\ApplicationHealthServiceInterface;

final readonly class ApplicationHealthService implements ApplicationHealthServiceInterface
{
    public function __construct(private ApplicationRepositoryInterface $applicationRepository)
    {
    }

    private static function now(): string
    {
        return (new \DateTimeImmutable())->format(DATE_ATOM);
    }

    /**
     * @return array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string}
     */
    public function buildHealth(): array
    {
        return [
            'status' => 'ok',
            'checks' => [
                'database' => [
                    'status' => 'up',
                    'message' => 'Connection check skipped for liveness.',
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
            $this->countApplications();

            return [
                'status' => 'ready',
                'checks' => [
                    'database' => [
                        'status' => 'up',
                        'message' => 'Doctrine ORM query verified.',
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

    private function countApplications(): int
    {
        return $this->applicationRepository->countAllApplications();
    }
}
