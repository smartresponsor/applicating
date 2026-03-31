param(
    [switch]$IncludeSmokes,
    [switch]$IncludeReports,
    [switch]$IncludeSecurity,
    [switch]$FailOnErrors
)

$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$projectRoot = Split-Path -Parent $root
$reportDir = Join-Path $projectRoot 'var\reports'
New-Item -ItemType Directory -Force -Path $reportDir | Out-Null

$steps = @(
    @{ Name = 'lint'; Command = 'composer lint' },
    @{ Name = 'lint:app-namespace'; Command = 'composer lint:app-namespace' },
    @{ Name = 'lint:config-prefix'; Command = 'composer lint:config-prefix' },
    @{ Name = 'lint:canonical-roots'; Command = 'composer lint:canonical-roots' },
    @{ Name = 'cs:check'; Command = 'composer cs:check' },
    @{ Name = 'stan'; Command = 'composer stan' },
    @{ Name = 'md'; Command = 'composer md' },
    @{ Name = 'test'; Command = 'composer test' }
)

if ($IncludeSmokes) {
    $steps += @(
        @{ Name = 'smoke:runtime'; Command = 'composer smoke:runtime' },
        @{ Name = 'smoke:container'; Command = 'composer smoke:container' },
        @{ Name = 'smoke:doctrine'; Command = 'composer smoke:doctrine' }
    )
}

if ($IncludeReports) {
    $steps += @(
        @{ Name = 'report:owner-overlap'; Command = 'composer report:owner-overlap' },
        @{ Name = 'report:route-inventory'; Command = 'composer report:route-inventory' },
        @{ Name = 'report:class-alias'; Command = 'composer report:class-alias' },
        @{ Name = 'report:runtime-proof'; Command = 'composer report:runtime-proof' },
        @{ Name = 'report:engineering-drift'; Command = 'composer report:engineering-drift' },
        @{ Name = 'report:pipeline-wiring'; Command = 'composer report:pipeline-wiring' },
        @{ Name = 'report:publish-guard'; Command = 'composer report:publish-guard' },
        @{ Name = 'report:fixture-publish-guard'; Command = 'composer report:fixture-publish-guard' }
    )
}

if ($IncludeSecurity) {
    $steps += @(
        @{ Name = 'composer-audit'; Command = 'composer audit' },
        @{ Name = 'importmap-audit'; Command = 'php bin/console importmap:audit' },
        @{ Name = 'gitleaks'; Command = 'gitleaks detect --no-banner --redact'; Optional = $true },
        @{ Name = 'semgrep'; Command = 'semgrep scan --config auto'; Optional = $true }
    )
}

$failed = @()
foreach ($step in $steps) {
    $logPath = Join-Path $reportDir ($step.Name + '.log')
    "=== " + $step.Name + " ===" | Set-Content -Path $logPath

    try {
        if ($step.Optional -and -not (Get-Command ($step.Command.Split(' ')[0]) -ErrorAction SilentlyContinue)) {
            "SKIPPED optional step: command not available" | Tee-Object -FilePath $logPath -Append | Out-Null
            continue
        }
        Invoke-Expression $step.Command 2>&1 | Tee-Object -FilePath $logPath -Append
        if ($LASTEXITCODE -ne 0) {
            $failed += $step.Name
        }
    } catch {
        $_ | Out-String | Tee-Object -FilePath $logPath -Append | Out-Null
        $failed += $step.Name
    }
}

"Application pipeline completed at $(Get-Date -Format o)" | Set-Content (Join-Path $reportDir 'pipeline-summary.log')

if ($failed.Count -gt 0) {
    Write-Host "Failed steps: $($failed -join ', ')"
    if ($FailOnErrors) {
        exit 1
    }
}
