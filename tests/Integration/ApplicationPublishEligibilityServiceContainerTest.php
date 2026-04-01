<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Service\ApplicationPublishEligibilityService;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationPublishEligibilityServiceContainerTest extends KernelTestCase
{
    public function testContainerResolvesPublishEligibilityServiceInterface(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationPublishEligibilityServiceInterface::class);

        self::assertInstanceOf(ApplicationPublishEligibilityService::class, $service);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
