<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Tools;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class CategorySecurityConfigBestPracticeTest extends TestCase
{
    public function testCatalogSecurityHasCoreSymfonySecurityHardeningOptions(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 2).'/config/packages/catalog_security.yaml');
        self::assertIsArray($config);
        $security = $config['security'] ?? [];
        self::assertIsArray($security);
        self::assertIsArray($security['access_decision_manager'] ?? null);
        self::assertIsArray($security['password_hashers'] ?? null);

        self::assertTrue($security['enable_authenticator_manager'] ?? null);
        self::assertTrue($security['erase_credentials'] ?? null);
        self::assertSame('unanimous', $security['access_decision_manager']['strategy'] ?? null);
        self::assertFalse($security['access_decision_manager']['allow_if_all_abstain'] ?? null);
        self::assertFalse($security['access_decision_manager']['allow_if_equal_granted_denied'] ?? null);
        self::assertSame(
            'auto',
            $security['password_hashers']['Symfony\\Component\\Security\\Core\\User\\PasswordAuthenticatedUserInterface'] ?? null
        );
    }

    public function testServiceConfigurationsUseSymfonyDiDefaults(): void
    {
        $infra = Yaml::parseFile(dirname(__DIR__, 2).'/config/packages/catalog_category_infra.yaml');
        $graphql = Yaml::parseFile(dirname(__DIR__, 2).'/config/catalog_category_services_graphql.yaml');
        self::assertIsArray($infra);
        self::assertIsArray($graphql);
        self::assertIsArray($infra['services'] ?? null);
        self::assertIsArray($graphql['services'] ?? null);
        self::assertIsArray($infra['services']['_defaults'] ?? null);
        self::assertIsArray($graphql['services']['_defaults'] ?? null);

        self::assertTrue($infra['services']['_defaults']['autowire'] ?? null);
        self::assertTrue($infra['services']['_defaults']['autoconfigure'] ?? null);
        self::assertFalse($infra['services']['_defaults']['public'] ?? null);

        self::assertTrue($graphql['services']['_defaults']['autowire'] ?? null);
        self::assertTrue($graphql['services']['_defaults']['autoconfigure'] ?? null);
        self::assertFalse($graphql['services']['_defaults']['public'] ?? null);
    }
}
