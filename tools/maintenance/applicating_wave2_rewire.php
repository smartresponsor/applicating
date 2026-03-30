<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$changed = [];

$composerPath = $root . '/composer.json';
if (is_file($composerPath)) {
    $composerJson = file_get_contents($composerPath);
    if (is_string($composerJson) && '' !== $composerJson) {
        /** @var array<string, mixed> $composer */
        $composer = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);
        /** @var array<string, string|array<int, string>> $scripts */
        $scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];

        $scripts['lint:canonical-roots'] = '@php tools/php/php84.php tools/linter/applicating_canonical_roots_check.php';
        $scripts['report:owner-overlap'] = '@php tools/php/php84.php tools/inspection/ApplicatingOwnerOverlapReport.php';
        $scripts['report:route-inventory'] = '@php tools/php/php84.php tools/inspection/ApplicatingRouteInventoryReport.php';
        $scripts['report:class-alias'] = '@php tools/php/php84.php tools/inspection/ApplicatingClassAliasReport.php';
        $scripts['report:runtime-proof'] = '@php tools/php/php84.php tools/inspection/ApplicatingRuntimeProofReport.php';
        $scripts['report:engineering-drift'] = '@php tools/php/php84.php tools/inspection/ApplicatingEngineeringDriftReport.php';
        $scripts['report:pipeline-wiring'] = '@php tools/php/php84.php tools/inspection/ApplicatingPipelineWiringReport.php';
        $scripts['report:publish-guard'] = '@php tools/php/php84.php tools/inspection/ApplicatingPublishGuardReport.php';
        $scripts['pipeline:inspection:applicating'] = 'powershell -ExecutionPolicy Bypass -File tools/ci/run-applicating-inspection-wave.ps1';

        $composer['scripts'] = $scripts;

        file_put_contents(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
        $changed[] = 'composer.json';
    }
}

$docsChangelogPath = $root . '/docs/CHANGELOG.md';
file_put_contents($docsChangelogPath, <<<'MD'
# CHANGELOG — Applicating / Application

## Overview
Applicating-oriented release baseline for the active Symfony application workspace:
- Application entity and lifecycle center
- Release, manifest and tenant-assignment flows
- Admin UI, API and CLI around application ecosystem management
- Applicating inspection, drift and pipeline reports
- Security, diagnostics and local quality pipeline

## Major Components
| Layer | Key Feature |
|-------|--------------|
| Domain | Application, Release, Manifest, Tenant assignment |
| Runtime | Symfony admin UI, API, forms and Twig management screens |
| QA | Applicating inspection reports, canonical roots lint, drift probes |
| Security | Voters, role-based management and publication controls |
| Demo | Fixtures, diagnostics and application lifecycle scenarios |
| Docs | Release notes, drift maps and patch manifests |

## Release Metadata
**Track:** Applicating / Application  
**Maintainer:** Smartresponsor Core  
MD
);
$changed[] = 'docs/CHANGELOG.md';

$docsReleasePath = $root . '/docs/RELEASE.md';
file_put_contents($docsReleasePath, <<<'MD'
# GitHub Release — Applicating / Application

To publish:
```bash
gh release create v18.0.0 --title "Applicating / Application" --notes-file docs/CHANGELOG.md --draft
```

Suggested artifacts to attach:
- applicating-application-runtime.zip
- applicating-application-roadmap.zip
- applicating-application-demo-bootstrap.zip
- applicating-application-inspection-reports.zip
MD
);
$changed[] = 'docs/RELEASE.md';

$tmpProbePath = $root . '/tmp-chatgpt-write-check.txt';
if (is_file($tmpProbePath)) {
    unlink($tmpProbePath);
    $changed[] = 'tmp-chatgpt-write-check.txt';
}

fwrite(STDOUT, "Applicating wave 2 rewrite touched:\n");
foreach ($changed as $file) {
    fwrite(STDOUT, " - {$file}\n");
}
