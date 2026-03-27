<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Tools;

use PHPUnit\Framework\TestCase;

final class CategoryLinterScriptsTest extends TestCase
{
    public function testMirrorCheckSkipsMissingLayerDirectories(): void
    {
        $projectRoot = $this->createProjectRoot();

        self::assertSame(0, $this->runScript('tools/linter/category_mirror_check.php', $projectRoot));
    }

    public function testPrefixCheckDetectsCategoryPrefixViolation(): void
    {
        $projectRoot = $this->createProjectRoot();
        $badFile = $projectRoot.'/src/Service/Category/InvalidName.php';
        mkdir(dirname($badFile), 0777, true);
        file_put_contents($badFile, "<?php\n");

        self::assertSame(1, $this->runScript('tools/linter/category_prefix_check.php', $projectRoot));
    }

    public function testCanonicalRootsCheckPassesForCanonicalStructure(): void
    {
        $projectRoot = $this->createProjectRoot();
        mkdir($projectRoot.'/src/Entity/Catalog', 0777, true);

        self::assertSame(0, $this->runScript('tools/linter/category_canonical_roots_check.php', $projectRoot));
    }

    public function testCanonicalRootsCheckDetectsForbiddenRoot(): void
    {
        $projectRoot = $this->createProjectRoot();
        mkdir($projectRoot.'/src/Catalog', 0777, true);

        self::assertSame(1, $this->runScript('tools/linter/category_canonical_roots_check.php', $projectRoot));
    }

    public function testCanonicalRootsCheckDetectsForbiddenNestedTestsPath(): void
    {
        $projectRoot = $this->createProjectRoot();
        mkdir($projectRoot.'/tests/Feature/Catalog', 0777, true);

        self::assertSame(1, $this->runScript('tools/linter/category_canonical_roots_check.php', $projectRoot));
    }

    public function testConfigPrefixCheckPassesForCatalogPrefixedYamlFiles(): void
    {
        $projectRoot = $this->createProjectRoot();
        $okFile = $projectRoot.'/config/packages/catalog_ok.yaml';
        mkdir(dirname($okFile), 0777, true);
        file_put_contents($okFile, "parameters: {}\n");

        self::assertSame(0, $this->runScript('tools/linter/catalog_config_prefix_check.php', $projectRoot));
    }

    public function testConfigPrefixCheckDetectsNonCatalogYamlFiles(): void
    {
        $projectRoot = $this->createProjectRoot();
        $badFile = $projectRoot.'/config/routes/category_move.yaml';
        mkdir(dirname($badFile), 0777, true);
        file_put_contents($badFile, "routes: {}\n");

        self::assertSame(1, $this->runScript('tools/linter/catalog_config_prefix_check.php', $projectRoot));
    }

    public function testConfigDirectoryInRepositoryHasNoPrefixViolations(): void
    {
        $projectRoot = dirname(__DIR__, 2);

        self::assertSame(0, $this->runScript('tools/linter/catalog_config_prefix_check.php', $projectRoot));
    }

    public function testAppNamespaceCheckPassesForCanonicalAppRoot(): void
    {
        $projectRoot = $this->createProjectRoot();
        mkdir($projectRoot.'/src', 0777, true);
        mkdir($projectRoot.'/tests', 0777, true);
        file_put_contents($projectRoot.'/composer.json', json_encode([
            'autoload' => ['psr-4' => ['App\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['App\\Tests\\' => 'tests/']],
        ], JSON_THROW_ON_ERROR));

        self::assertSame(0, $this->runScript('tools/linter/app_namespace_check.php', $projectRoot));
    }

    public function testAppNamespaceCheckDetectsForbiddenNamespaceRoot(): void
    {
        $projectRoot = $this->createProjectRoot();
        mkdir($projectRoot.'/src', 0777, true);
        mkdir($projectRoot.'/tests', 0777, true);
        file_put_contents($projectRoot.'/composer.json', json_encode([
            'autoload' => ['psr-4' => ['App\\' => 'src/', 'Smartresponsor\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['App\\Tests\\' => 'tests/']],
        ], JSON_THROW_ON_ERROR));

        self::assertSame(1, $this->runScript('tools/linter/app_namespace_check.php', $projectRoot));
    }

    public function testRepositoryHasCanonicalAppNamespaceRoot(): void
    {
        $projectRoot = dirname(__DIR__, 2);

        self::assertSame(0, $this->runScript('tools/linter/app_namespace_check.php', $projectRoot));
    }

    private function runScript(string $scriptPath, string $projectRoot): int
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($scriptPath).' '.escapeshellarg($projectRoot);
        exec($command, $output, $exitCode);

        return $exitCode;
    }

    private function createProjectRoot(): string
    {
        $path = sys_get_temp_dir().'/cataloging-lint-'.bin2hex(random_bytes(8));
        mkdir($path, 0777, true);

        return $path;
    }
}
