<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\Entity\Application;
use App\Applicating\ServiceInterface\ApplicationHealthServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ApplicationHealthService implements ApplicationHealthServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
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
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(application.id)')
            ->from(Application::class, 'application')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
