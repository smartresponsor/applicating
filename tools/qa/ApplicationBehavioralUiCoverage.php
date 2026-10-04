<?php

declare(strict_types=1);

/**
 * Produces the Canon042 behavioral/UI coverage inventory from repository-owned test evidence.
 *
 * The eligible surface is explicit and stable. Covered identifiers are admitted only when the
 * corresponding executable test token or coverage marker exists in the current repository tree.
 */

$root = dirname(__DIR__, 2);

$read = static function (string $relativePath) use ($root): string {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    return is_file($path) ? (string) file_get_contents($path) : '';
};

$files = [
    'functional_health' => $read('tests/Functional/ApplicationHealthControllerTest.php'),
    'functional_all' => '',
    'browser_all' => '',
];

$collectPhp = static function (string $relativeDirectory) use ($root): string {
    $directory = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
    if (!is_dir($directory)) {
        return '';
    }

    $content = '';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        if (!in_array(strtolower($file->getExtension()), ['php', 'ts', 'js', 'mts', 'mjs'], true)) {
            continue;
        }

        $content .= "\n".(string) file_get_contents($file->getPathname());
    }

    return $content;
};

$files['functional_all'] = $collectPhp('tests/Functional');
$files['browser_all'] = $collectPhp('tests/Panther').$collectPhp('tests/Playwright');

$containsAny = static function (string $haystack, array $needles): bool {
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }

    return false;
};

$functional = [
    'eligible' => [
        'route:health',
        'route:ready',
        'route:login',
        'route:admin-applications',
        'route:application-api-index',
        'route:application-api-report',
        'route:application-readiness-api',
    ],
    'covered' => [],
];

$functionalEvidence = [
    'route:health' => ["'/health'", '"/health"'],
    'route:ready' => ["'/ready'", '"/ready"'],
    'route:login' => ["'/login'", '"/login"'],
    'route:admin-applications' => ["'/admin/applications'", '"/admin/applications"'],
    'route:application-api-index' => ["'/api/applicating/application'", '"/api/applicating/application"'],
    'route:application-api-report' => ["'/api/applicating/application/report'", '"/api/applicating/application/report"'],
    'route:application-readiness-api' => ['/api/applicating/application/readiness/'],
];

foreach ($functionalEvidence as $identifier => $needles) {
    if ($containsAny($files['functional_all'], $needles)) {
        $functional['covered'][] = $identifier;
    }
}

$behavioral = [
    'eligible' => [
        'workflow:application-create-edit',
        'workflow:release-publish',
        'workflow:manifest-governance',
        'workflow:tenant-assignment-toggle',
        'workflow:admin-authentication',
    ],
    'covered' => [],
];

$ui = [
    'eligible' => [
        'surface:login',
        'surface:application-list',
        'surface:application-detail',
        'surface:application-edit',
    ],
    'covered' => [],
];

foreach ($behavioral['eligible'] as $identifier) {
    if (str_contains($files['browser_all'], '@behavioral-coverage '.$identifier)) {
        $behavioral['covered'][] = $identifier;
    }
}

foreach ($ui['eligible'] as $identifier) {
    if (str_contains($files['browser_all'], '@ui-coverage '.$identifier)) {
        $ui['covered'][] = $identifier;
    }
}

$critical = [
    'eligible' => [
        'workflow:release-publish',
        'workflow:tenant-assignment-toggle',
    ],
    'covered' => array_values(array_intersect(
        ['workflow:release-publish', 'workflow:tenant-assignment-toggle'],
        $behavioral['covered'],
    )),
];

$evidence = [
    'schema' => 'behavioral-ui-coverage-v2',
    'generatedAt' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
    'producer' => [
        'kind' => 'repository_script',
        'script' => 'test:behavioral-coverage',
    ],
    'dimensions' => [
        'functional' => $functional,
        'behavioral' => $behavioral,
        'ui' => $ui,
        'critical' => $critical,
    ],
];

$outputDirectory = $root.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'coverage';
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true) && !is_dir($outputDirectory)) {
    fwrite(STDERR, "Unable to create var/coverage.\n");
    exit(1);
}

$json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
file_put_contents($outputDirectory.DIRECTORY_SEPARATOR.'behavioral-ui.json', $json);

printf(
    "Behavioral/UI evidence written: functional %d/%d, behavioral %d/%d, ui %d/%d, critical %d/%d.\n",
    count($functional['covered']),
    count($functional['eligible']),
    count($behavioral['covered']),
    count($behavioral['eligible']),
    count($ui['covered']),
    count($ui['eligible']),
    count($critical['covered']),
    count($critical['eligible']),
);
