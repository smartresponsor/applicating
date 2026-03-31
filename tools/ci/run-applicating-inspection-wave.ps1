param(
    [switch]$FailOnErrors
)

$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$projectRoot = Split-Path -Parent $root
$reportDir = Join-Path $projectRoot 'var\reports\applicating-wave'
New-Item -ItemType Directory -Force -Path $reportDir | Out-Null

$steps = @(
    @{ Name = 'lint:app-namespace'; Command = '@php tools/php/php84.php tools/linter/app_namespace_check.php' },
    @{ Name = 'lint:applicating-canonical-roots'; Command = '@php tools/php/php84.php tools/linter/applicating_canonical_roots_check.php' },
    @{ Name = 'report:applicating-route-inventory'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingRouteInventoryReport.php' },
    @{ Name = 'report:applicating-class-alias'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingClassAliasReport.php' },
    @{ Name = 'report:applicating-runtime-proof'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingRuntimeProofReport.php' },
    @{ Name = 'report:applicating-owner-overlap'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingOwnerOverlapReport.php' },
    @{ Name = 'report:applicating-engineering-drift'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingEngineeringDriftReport.php' },
    @{ Name = 'report:applicating-pipeline-wiring'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingPipelineWiringReport.php' },
    @{ Name = 'report:applicating-publish-guard'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingPublishGuardReport.php' },
    @{ Name = 'report:applicating-fixture-publish-guard'; Command = '@php tools/php/php84.php tools/inspection/ApplicatingFixturePublishGuardReport.php' }
)

$failed = @()
foreach ($step in $steps) {
    $logPath = Join-Path $reportDir ($step.Name + '.log')
    "=== " + $step.Name + " ===" | Set-Content -Path $logPath

    try {
        $composerCommand = $step.Command.Replace('@php ', 'php ')
        Invoke-Expression $composerCommand 2>&1 | Tee-Object -FilePath $logPath -Append
        if ($LASTEXITCODE -ne 0) {
            $failed += $step.Name
        }
    } catch {
        $_ | Out-String | Tee-Object -FilePath $logPath -Append | Out-Null
        $failed += $step.Name
    }
}

$summaryPath = Join-Path $reportDir 'summary.log'
"Applicating inspection wave completed at $(Get-Date -Format o)" | Set-Content $summaryPath
if ($failed.Count -gt 0) {
    "Failed steps: $($failed -join ', ')" | Add-Content $summaryPath
    Write-Host "Failed steps: $($failed -join ', ')"
    if ($FailOnErrors) {
        exit 1
    }
} else {
    'All Applicating inspection steps passed.' | Add-Content $summaryPath
}
