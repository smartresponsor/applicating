<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Request;

use App\Request\CategoryAttachmentAddRequest;
use App\Request\CategoryBulkRequest;
use App\Request\CategoryCollectionRequest;
use App\Request\CategoryRulePreviewRequest;
use App\Request\WebhookDispatchRequest;
use PHPUnit\Framework\TestCase;

final class BoundaryRequestDtoTest extends TestCase
{
    public function testRulePreviewRejectsInvalidJsonPayload(): void
    {
        $input = CategoryRulePreviewRequest::fromJson('{');

        self::assertFalse($input->isValid());
        self::assertSame(['bad_spec'], $input->getErrors());
    }

    public function testCollectionRequestAcceptsBlankPayloadAsEmptyRules(): void
    {
        $input = CategoryCollectionRequest::fromJson('');

        self::assertTrue($input->isValid());
        self::assertSame([], $input->rules);
    }

    public function testBulkRequestValidatesIdsAndActionTypes(): void
    {
        $input = CategoryBulkRequest::fromJson('{"ids":"invalid","action":0}');

        self::assertFalse($input->isValid());
        self::assertSame(['ids must be an array', 'action must be a non-empty string'], $input->getErrors());
        self::assertSame([], $input->ids);
        self::assertSame('publish', $input->action);
    }

    public function testWebhookRequestUsesDefaultsForEmptyPayload(): void
    {
        $input = WebhookDispatchRequest::fromJson('');

        self::assertTrue($input->isValid());
        self::assertSame('category.updated', $input->event);
        self::assertSame('http://localhost:8081/hook', $input->endpoint);
        self::assertSame(['id' => 1], $input->payload);
    }

    public function testAttachmentRequestValidatesRequiredFields(): void
    {
        $input = CategoryAttachmentAddRequest::fromJson('{"type":"banner"}');

        self::assertFalse($input->isValid());
        self::assertSame(['category_id is required', 'path is required'], $input->getErrors());
    }
}
