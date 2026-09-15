<?php

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$composer = json_decode((string) file_get_contents('composer.json'), true, flags: JSON_THROW_ON_ERROR);
$packageName = (string) ($composer['name'] ?? '');
[, $subjectToken] = array_pad(explode('/', $packageName, 2), 2, '');
$subjectPrefix = str_replace('-', '_', strtolower($subjectToken)) . '_';

$conventionalBootstrapNames = [
    'framework.yaml', 'services.yaml', 'services_dev.yaml', 'services_test.yaml',
    'routes.yaml', 'routes_dev.yaml', 'routes_test.yaml', 'doctrine.yaml',
    'doctrine_migrations.yaml', 'security.yaml', 'twig.yaml', 'messenger.yaml',
    'monolog.yaml', 'routing.yaml', 'validator.yaml', 'translation.yaml',
    'mailer.yaml', 'notifier.yaml', 'cache.yaml', 'csrf.yaml', 'lock.yaml',
    'asset_mapper.yaml', 'property_info.yaml', 'twig_component.yaml', 'ux_turbo.yaml',
    'rate_limiter.yaml', 'api_platform.yaml', 'nelmio_api_doc.yaml', 'scheb_2fa.yaml',
    'easyadmin.yaml', 'web_profiler.yaml', 'controllers.yaml', 'annotations.yaml',
    'reset_password.yaml', 'verify_email.yaml',
];

$configFiles = [];
$issues = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('config', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || !in_array(strtolower($fileInfo->getExtension()), ['yaml', 'yml'], true)) {
        continue;
    }

    $relativePath = str_replace('\\', '/', substr($fileInfo->getPathname(), strlen('config') + 1));
    $configFiles[] = $relativePath;
    $filename = $fileInfo->getFilename();
    if (!str_starts_with($filename, $subjectPrefix) && !in_array($filename, $conventionalBootstrapNames, true)) {
        $issues[] = $relativePath;
    }
}

sort($configFiles);
sort($issues);

$report = [
    'tool' => 'applicating_config_prefix_check',
    'files' => $configFiles,
    'issues' => $issues,
    'status' => [] === $issues ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-config-prefix-check.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $issues ? 0 : 1);
