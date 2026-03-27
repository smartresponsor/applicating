<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category\Infrastructure;

use App\Infrastructure\ProductWebhookPublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WebhookPublisherTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $client = new MockHttpClient([new MockResponse('{}', ['http_code' => 200])]);
        $pub = new ProductWebhookPublisher($client, 'http://example');
        $pub->publish(['event' => 'category.changed']);
        $this->assertTrue(true);
    }
}
