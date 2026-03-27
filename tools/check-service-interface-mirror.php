<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

$root = dirname(__DIR__);
$serviceRoot = $root . '/src/Service';
$serviceInterfaceRoot = $root . '/src/ServiceInterface';

$collectPhpFiles = static function (string $directory): array {
    if (!is_dir($directory)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo || $fileInfo->getExtension() !== 'php') {
            continue;
        }

        $files[] = $fileInfo->getPathname();
    }

    sort($files);

    return $files;
};

$toRelative = static fn (string $path): string => ltrim(str_replace($root, '', $path), '/');

$serviceFiles = $collectPhpFiles($serviceRoot);
$serviceInterfaceFiles = $collectPhpFiles($serviceInterfaceRoot);

$interfacesInService = [];
foreach ($serviceFiles as $serviceFile) {
    $contents = file_get_contents($serviceFile);
    if ($contents === false) {
        continue;
    }

    if (preg_match('/\binterface\s+[A-Za-z_][A-Za-z0-9_]*/', $contents) === 1) {
        $interfacesInService[] = $toRelative($serviceFile);
    }
}

$missingMirrors = [];
foreach ($serviceFiles as $serviceFile) {
    $relative = substr($serviceFile, strlen($serviceRoot) + 1);
    $pathInfo = pathinfo($relative);
    $interfaceFile = $serviceInterfaceRoot
        . ($pathInfo['dirname'] !== '.' ? '/' . $pathInfo['dirname'] : '')
        . '/' . $pathInfo['filename']
        . 'Interface.php';

    if (!file_exists($interfaceFile)) {
        $missingMirrors[] = [
            'service' => $toRelative($serviceFile),
            'expectedInterface' => $toRelative($interfaceFile),
        ];
    }
}

$orphanInterfaces = [];
foreach ($serviceInterfaceFiles as $interfaceFile) {
    $relative = substr($interfaceFile, strlen($serviceInterfaceRoot) + 1);
    $pathInfo = pathinfo($relative);

    if (!str_ends_with($pathInfo['filename'], 'Interface')) {
        continue;
    }

    $serviceFilename = substr($pathInfo['filename'], 0, -strlen('Interface'));
    $serviceFile = $serviceRoot
        . ($pathInfo['dirname'] !== '.' ? '/' . $pathInfo['dirname'] : '')
        . '/' . $serviceFilename
        . '.php';

    if (!file_exists($serviceFile)) {
        $orphanInterfaces[] = [
            'interface' => $toRelative($interfaceFile),
            'expectedService' => $toRelative($serviceFile),
        ];
    }
}

$report = [
    'interfaces_in_service' => $interfacesInService,
    'missing_mirror_interfaces' => $missingMirrors,
    'orphan_service_interfaces' => $orphanInterfaces,
    'summary' => [
        'service_files' => count($serviceFiles),
        'service_interface_files' => count($serviceInterfaceFiles),
        'interfaces_in_service_count' => count($interfacesInService),
        'missing_mirror_interfaces_count' => count($missingMirrors),
        'orphan_service_interfaces_count' => count($orphanInterfaces),
    ],
];

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
