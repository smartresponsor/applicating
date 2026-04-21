<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

require __DIR__ . '/../_support/bootstrap.php';

$serviceDir = __DIR__ . '/../../src/Service';
$interfaceDir = __DIR__ . '/../../src/ServiceInterface';

$services = [];
foreach (new DirectoryIterator($serviceDir) as $entry) {
    if (!$entry->isFile() || 'php' !== $entry->getExtension()) {
        continue;
    }

    $services[] = $entry->getBasename('.php');
}

sort($services);
$missingInterfaces = [];
foreach ($services as $serviceClass) {
    $expectedInterface = sprintf('%sInterface.php', $serviceClass);
    if (!is_file($interfaceDir . '/' . $expectedInterface)) {
        $missingInterfaces[] = sprintf('App\\Service\\%s -> App\\ServiceInterface\\%sInterface', $serviceClass, $serviceClass);
    }
}

$report = [
    'tool' => 'applicating_service_interface_parity_check',
    'serviceCount' => count($services),
    'missingInterfaces' => $missingInterfaces,
    'status' => [] === $missingInterfaces ? 'complete' : 'incomplete',
];

applicating_write_json('report/inspection/applicating-service-interface-parity-check.json', $report);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit([] === $missingInterfaces ? 0 : 1);
